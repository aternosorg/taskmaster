<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Proxy\ProxyWorker;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;

abstract class Worker implements WorkerInterface
{
    protected Taskmaster $taskmaster;
    protected ?WorkerInstanceInterface $instance = null;
    protected ?string $group = null;
    protected ?ProxyInterface $proxy = null;

    /**
     * @param Taskmaster $taskmaster
     * @return $this
     */
    public function setTaskmaster(Taskmaster $taskmaster): static
    {
        $this->taskmaster = $taskmaster;
        return $this;
    }

    /**
     * @return WorkerInstanceInterface
     */
    public function getInstance(): WorkerInstanceInterface
    {
        if ($this->instance === null || $this->instance->getStatus() === WorkerStatus::FAILED) {
            $this->instance = $this->createInstance();
            if ($proxy = $this->getProxy()) {
                $this->instance = (new ProxyWorker($this->taskmaster->getOptions()))->setWorker($this->instance)->setProxy($proxy);
            }
            $this->instance->init()->start();
        }
        return $this->instance;
    }

    /**
     * @return $this
     */
    public function update(): static
    {
        $this->getInstance()->update();
        return $this;
    }

    /**
     * @return $this
     */
    public function stop(): static
    {
        $this->getInstance()->stop();
        return $this;
    }

    /**
     * @return WorkerStatus
     */
    public function getStatus(): WorkerStatus
    {
        return $this->getInstance()->getStatus();
    }

    /**
     * @param TaskInterface $task
     * @return $this
     */
    public function assignTask(TaskInterface $task): static
    {
        $this->getInstance()->runTask($task);
        return $this;
    }

    /**
     * @return ProxyInterface|null
     */
    public function getProxy(): ?ProxyInterface
    {
        return $this->proxy;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     * @return $this
     */
    public function setGroup(?string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param ProxyInterface|null $proxy
     * @return $this
     */
    public function setProxy(?ProxyInterface $proxy): static
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        $this->instance = null;
    }

    abstract protected function createInstance(): WorkerInstanceInterface;
}