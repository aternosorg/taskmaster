<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Proxy\ProxiedSocket;
use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;

abstract class Worker implements WorkerInterface
{
    protected WorkerStatus $status = WorkerStatus::AVAILABLE;
    protected Taskmaster $taskmaster;
    protected ?WorkerInstanceInterface $instance = null;
    protected ?string $group = null;
    protected ?ProxyInterface $proxy = null;
    protected bool $instanceStarted = false;
    protected ?TaskInterface $queuedTask = null;

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
        if ($this->instance === null || $this->instance->getStatus() === WorkerInstanceStatus::FAILED) {
            $this->instanceStarted = false;
            $this->instance = $this->createInstance();
            if ($this->queuedTask) {
                $this->startInstance();
            } else {
                $this->status = WorkerStatus::AVAILABLE;
            }
        }
        return $this->instance;
    }

    /**
     * @return void
     */
    protected function startInstance(): void
    {
        $this->instanceStarted = true;
        $instance = $this->getInstance();
        if (!$this->proxy) {
            $instance->init()->start();
            return;
        }

        if (!$instance instanceof ProxyableWorkerInstanceInterface) {
            throw new \RuntimeException("Worker instance must implement ProxyableWorkerInstanceInterface to be used with a proxy.");
        }

        $socket = new ProxiedSocket($this->proxy->getProxySocket(), $instance->getId());

        $this->proxy->startWorkerInstance($instance)
            ->then(function () use ($instance, $socket) {
                $instance->setSocket($socket);
                $instance->init();
                $instance->setStatus(WorkerInstanceStatus::STARTING);
            });
    }

    /**
     * @return $this
     */
    public function update(): static
    {
        $instance = $this->getInstance();
        if (!in_array($instance->getStatus(), WorkerInstanceStatus::GROUP_UPDATE)) {
            return $this;
        }
        $instance->update();
        if ($instance->getStatus() === WorkerInstanceStatus::IDLE) {
            if ($this->queuedTask) {
                $instance->runTask($this->queuedTask);
                $this->queuedTask = null;
            } else {
                $this->status = WorkerStatus::AVAILABLE;
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function stop(): static
    {
        $this->status = WorkerStatus::AVAILABLE;
        $instance = $this->getInstance();
        $this->instance = null;
        if (!$this->proxy) {
            $instance->stop();
            return $this;
        }
        if (!$instance instanceof ProxyableWorkerInstanceInterface) {
            throw new \RuntimeException("Worker instance must implement ProxyableWorkerInstanceInterface to be used with a proxy.");
        }
        $this->proxy->stopWorkerInstance($instance);
        return $this;
    }

    /**
     * @return WorkerStatus
     */
    public function getStatus(): WorkerStatus
    {
        return $this->status;
    }

    /**
     * @param TaskInterface $task
     * @return $this
     */
    public function assignTask(TaskInterface $task): static
    {
        $this->status = WorkerStatus::WORKING;
        $this->queuedTask = $task;
        $instance = $this->getInstance();
        if (!$this->instanceStarted) {
            $this->startInstance();
        }
        if ($instance->getStatus() === WorkerInstanceStatus::IDLE) {
            $this->getInstance()->runTask($task);
            $this->queuedTask = null;
        }
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