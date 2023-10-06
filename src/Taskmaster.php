<?php

namespace Aternos\Taskmaster;

use Aternos\Taskmaster\Environment\EnvironmentInterface;
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
    protected ?EnvironmentInterface $environment = null;
    protected ?int $parallelLimit = null;

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
     * @param EnvironmentInterface $environment
     * @return $this
     */
    public function setEnvironment(EnvironmentInterface $environment): static
    {
        $environment->setTaskmaster($this);
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return $this
     */
    public function start(): static
    {
        while ($task = $this->getNextTask()) {
            $worker = $this->getAvailableWorker();
            $worker->runTask($task);
        }
        do {
            $working = 0;
            foreach ($this->workers as $worker) {
                $worker->update();
                if ($worker->getStatus() === WorkerStatus::WORKING) {
                    $working++;
                }
            }
        } while ($working > 0);
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
        return $this;
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
        if (count($this->workers) < $this->getParallelLimit()) {
            $worker = $this->environment->createWorker();
            $this->workers[] = $worker;
            return $worker;
        }
        return null;
    }

    /**
     * @return $this
     */
    public function wait(): static
    {
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
}