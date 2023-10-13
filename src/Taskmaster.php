<?php

namespace Aternos\Taskmaster;

use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Task\TaskFactoryInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Worker\WorkerInterface;
use Aternos\Taskmaster\Worker\WorkerStatus;

class Taskmaster
{
    /**
     * @var TaskInterface[]
     */
    protected array $tasks = [];

    /**
     * @var WorkerInterface[]
     */
    protected array $workers = [];

    protected ?TaskFactoryInterface $taskFactory = null;
    protected ?int $parallelLimit = null;
    protected TaskmasterOptions $options;
    protected ?ProxyInterface $proxy = null;

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
    public function run(): static
    {
        while ($task = $this->getNextTask()) {
            $worker = $this->waitForAvailableWorker();
            $worker->assignTask($task);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function wait(): static
    {
        while ($this->hasRunningWorkers()) {
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
        $this->proxy?->stop();
        return $this;
    }

    /**
     * @return bool
     */
    protected function hasRunningWorkers(): bool
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
    protected function update(): void
    {
        foreach ($this->workers as $worker) {
            $worker->update();
        }
        $this->proxy?->update();
        usleep(500);
    }

    /**
     * @return WorkerInterface
     */
    protected function waitForAvailableWorker(): WorkerInterface
    {
        do {
            $this->update();
            $worker = $this->getAvailableWorker();
        } while ($worker === null);
        return $worker;
    }

    /**
     * @return WorkerInterface|null
     */
    protected function getAvailableWorker(): ?WorkerInterface
    {
        foreach ($this->workers as $worker) {
            if ($worker->getStatus() === WorkerStatus::IDLE) {
                return $worker;
            }
        }
        return null;
    }

    /**
     * @param WorkerInterface[] $workers
     * @return $this
     */
    public function setWorkers(array $workers): static
    {
        foreach ($workers as $worker) {
            $worker->setTaskmaster($this);
        }
        $this->workers = $workers;
        return $this;
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
     * @return int
     */
    public function getParallelLimit(): int
    {
        return $this->parallelLimit ?? 8;
    }

    /**
     * @param int|null $parallelLimit
     * @return $this
     */
    public function setParallelLimit(?int $parallelLimit): static
    {
        $this->parallelLimit = $parallelLimit;
        return $this;
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

    /**
     * @param ProxyInterface|null $proxy
     * @return $this
     */
    public function setProxy(?ProxyInterface $proxy): static
    {
        $proxy->setOptions($this->options)->start();
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return ProxyInterface|null
     */
    public function getProxy(): ?ProxyInterface
    {
        return $this->proxy;
    }

    public function getOptions(): TaskmasterOptions
    {
        return $this->options;
    }
}