<?php

namespace Aternos\Taskmaster;

use Aternos\Taskmaster\Communication\Socket\SelectableSocketInterface;
use Aternos\Taskmaster\Environment\Fork\ForkWorker;
use Aternos\Taskmaster\Environment\Process\ProcessWorker;
use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Task\TaskFactoryInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Worker\SocketWorkerInterface;
use Aternos\Taskmaster\Worker\WorkerInterface;
use Aternos\Taskmaster\Worker\WorkerStatus;

class Taskmaster
{
    public const SOCKET_WAIT_TIME = 500;

    /**
     * @var TaskInterface[]
     */
    protected array $tasks = [];

    /**
     * @var WorkerInterface[]
     */
    protected array $workers = [];

    /**
     * @var ProxyInterface[]
     */
    protected array $proxies = [];

    protected ?TaskFactoryInterface $taskFactory = null;
    protected TaskmasterOptions $options;

    public function __construct()
    {
        $this->options = new TaskmasterOptions();
    }

    /**
     * @param TaskInterface ...$task
     * @return $this
     */
    public function addTask(TaskInterface ...$task): static
    {
        foreach ($task as $t) {
            $this->tasks[] = $t;
        }
        return $this;
    }

    /**
     * @param TaskFactoryInterface $taskFactory
     * @return $this
     */
    public function setTaskFactory(TaskFactoryInterface $taskFactory): static
    {
        $this->taskFactory = $taskFactory;
        return $this;
    }

    /**
     * @return $this
     */
    public function wait(): static
    {
        $this->update();
        while ($this->isWorking()) {
            $this->update();
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function stop(): static
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
        foreach ($this->proxies as $proxy) {
            $proxy->stop();
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isWorking(): bool
    {
        return count($this->getRunningWorkers()) > 0;
    }

    /**
     * @return array
     */
    protected function getRunningWorkers(): array
    {
        $runningWorkers = [];
        foreach ($this->workers as $worker) {
            if ($worker->getStatus() === WorkerStatus::WORKING) {
                $runningWorkers[] = $worker;
            }
        }
        return $runningWorkers;
    }

    /**
     * @return void
     */
    public function update(): void
    {
        foreach ($this->workers as $worker) {
            $this->assignNextTaskToWorkerIfPossible($worker);
            $worker->update();
            $this->assignNextTaskToWorkerIfPossible($worker);
        }
        foreach ($this->proxies as $proxy) {
            $proxy->update();
        }
        $this->waitForNewUpdate();
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    protected function assignNextTaskToWorkerIfPossible(WorkerInterface $worker): void
    {
        if ($worker->getStatus() !== WorkerStatus::AVAILABLE) {
            return;
        }
        $task = $this->getNextTask();
        if (!$task) {
            return;
        }
        $worker->assignTask($task);
    }

    /**
     * @return void
     */
    protected function waitForNewUpdate(): void
    {
        $time = Taskmaster::SOCKET_WAIT_TIME;
        $streams = $this->getSelectableStreams();
        if (count($streams) === 0) {
            usleep($time);
            return;
        }
        stream_select($streams, $write, $except, 0, $time);
    }

    /**
     * @return resource[]
     */
    protected function getSelectableStreams(): array
    {
        $streams = [];
        foreach ($this->workers as $worker) {
            if (!$worker instanceof SocketWorkerInterface) {
                continue;
            }
            $socket = $worker->getSocket();
            if (!$socket) {
                continue;
            }
            if (!$socket instanceof SelectableSocketInterface) {
                continue;
            }
            $streams[] = $socket->getSelectableReadStream();
        }
        foreach ($this->proxies as $proxy) {
            $socket = $proxy->getSocket();
            if (!$socket) {
                continue;
            }
            if (!$socket instanceof SelectableSocketInterface) {
                continue;
            }
            $streams[] = $socket->getSelectableReadStream();
        }
        return $streams;
    }

    /**
     * @param WorkerInterface[] $workers
     * @return $this
     */
    public function setWorkers(array $workers): static
    {
        $this->workers = [];
        foreach ($workers as $worker) {
            $this->addWorker($worker);
        }
        return $this;
    }

    /**
     * @param WorkerInterface $worker
     * @return $this
     */
    public function addWorker(WorkerInterface $worker): static
    {
        $proxy = $worker->getProxy();
        if ($proxy && !in_array($proxy, $this->proxies, true)) {
            if (!$proxy->isRunning()) {
                $proxy->setOptions($this->options);
                $proxy->start();
            }
            $this->proxies[] = $proxy;
        }

        $worker->setTaskmaster($this);
        $this->workers[] = $worker;
        return $this;
    }

    /**
     * @param WorkerInterface $worker
     * @param int $count
     * @return $this
     */
    public function addWorkers(WorkerInterface $worker, int $count): static
    {
        for ($i = 0; $i < $count; $i++) {
            $this->addWorker(clone $worker);
        }
        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function autoDetectWorkers(int $count): static
    {
        if (extension_loaded("pcntl")) {
            return $this->addWorkers(new ForkWorker(), $count);
        }
        if (getenv("TASKMASTER_PROXY_FORK")) {
            $proxy = new ProcessProxy();
            return $this->addWorkers((new ForkWorker())->setProxy($proxy), $count);
        }
        return $this->addWorkers(new ProcessWorker(), $count);
    }

    /**
     * @return TaskInterface|null
     */
    public function getNextTask(): ?TaskInterface
    {
        if ($this->taskFactory !== null) {
            return $this->taskFactory->createNextTask();
        }
        if (count($this->tasks) > 0) {
            return array_shift($this->tasks);
        }
        return null;
    }

    /**
     * @param string|null $bootstrap
     * @return $this
     */
    public function setBootstrap(?string $bootstrap): static
    {
        $this->options->setBootstrap($bootstrap);
        return $this;
    }

    /**
     * @param string $phpExecutable
     * @return $this
     */
    public function setPhpExecutable(string $phpExecutable): static
    {
        $this->options->setPhpExecutable($phpExecutable);
        return $this;
    }

    public function getOptions(): TaskmasterOptions
    {
        return $this->options;
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }
}