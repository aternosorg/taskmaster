<?php

namespace Aternos\Taskmaster;

use Aternos\Taskmaster\Environment\EnvironmentInterface;
use Aternos\Taskmaster\Task\TaskFactoryInterface;
use Aternos\Taskmaster\Task\TaskInterface;

class Taskmaster
{
    /**
     * @var TaskInterface[]
     */
    protected array $tasks = [];
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
        $this->environment->start();
        return $this;
    }

    /**
     * @return $this
     */
    public function wait(): static
    {
        $this->environment->wait();
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