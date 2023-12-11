<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Proxy\ProxiedSocket;
use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Proxy\ProxyStatus;
use Aternos\Taskmaster\Task\TaskFactoryInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceStatus;
use RuntimeException;
use Throwable;

/**
 * Class Worker
 *
 * A worker runs tasks in a {@link RuntimeInterface}.
 * It starts a {@link WorkerInstanceInterface} when necessary and assigns tasks to it.
 * When the worker instance dies, the worker will start a new one.
 * The worker can use a {@link ProxyInterface} to run the worker instance.
 *
 * @package Aternos\Taskmaster\Worker
 */
abstract class Worker implements WorkerInterface
{
    protected WorkerStatus $status = WorkerStatus::AVAILABLE;
    protected ?TaskmasterOptions $options = null;
    protected ?WorkerInstanceInterface $instance = null;
    protected ?string $group = null;
    protected ?ProxyInterface $proxy = null;
    protected bool $instanceStarted = false;
    protected ?TaskInterface $queuedTask = null;
    protected ?TaskFactoryInterface $initTaskFactory = null;
    protected ?TaskInterface $initTask = null;

    /**
     * @inheritDoc
     */
    public function setOptions(TaskmasterOptions $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptionsIfNecessary(TaskmasterOptions $options): static
    {
        if ($this->options === null) {
            $this->setOptions($options);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setInitTaskFactory(?TaskFactoryInterface $initTaskFactory): static
    {
        $this->initTaskFactory = $initTaskFactory;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setInitTaskFactoryIfNecessary(?TaskFactoryInterface $initTaskFactory): static
    {
        if ($this->initTaskFactory === null) {
            $this->setInitTaskFactory($initTaskFactory);
        }
        return $this;
    }

    /**
     * Get the current instance or create a new one if necessary
     *
     * The instance is only started if there is a queued task.
     * If the current instance failed, a new one will be created as well.
     *
     * @return WorkerInstanceInterface
     */
    protected function getInstance(): WorkerInstanceInterface
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
     * Start the worker instance
     *
     * If a proxy is set, the worker instance will be started on the proxy.
     *
     * @return void
     */
    protected function startInstance(): void
    {
        $this->instanceStarted = true;
        $instance = $this->getInstance();

        if ($this->initTaskFactory) {
            $this->initTask = $this->initTaskFactory->createNextTask(null);
        }

        if (!$this->proxy) {
            $instance->init()->start();
            return;
        }

        if (!$instance instanceof ProxyableWorkerInstanceInterface) {
            throw new RuntimeException("Worker instance must implement ProxyableWorkerInstanceInterface to be used with a proxy.");
        }

        $this->proxy->startWorkerInstance($instance)
            ->then(function () use ($instance) {
                $instance->setSocket(new ProxiedSocket($this->proxy->getProxySocket(), $instance->getId()));
                $instance->init();
                $instance->setStatus(WorkerInstanceStatus::STARTING);
            });
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function update(): static
    {
        $instance = $this->getInstance();
        if (!in_array($instance->getStatus(), WorkerInstanceStatus::GROUP_UPDATE)) {
            return $this;
        }
        $instance->update();
        if ($instance->getStatus() === WorkerInstanceStatus::IDLE) {
            if ($this->initTask) {
                $instance->runTask($this->initTask);
                $this->initTask = null;
            } elseif ($this->queuedTask) {
                $instance->runTask($this->queuedTask);
                $this->queuedTask = null;
            } else {
                $this->status = WorkerStatus::AVAILABLE;
            }
        }
        if ($this->proxy?->getStatus() === ProxyStatus::FAILED) {
            $this->instance->handleFail("Proxy failed.");
            $this->instance = null;
        }
        return $this;
    }

    /**
     * @inheritDoc
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
            throw new RuntimeException("Worker instance must implement ProxyableWorkerInstanceInterface to be used with a proxy.");
        }
        $this->proxy->stopWorkerInstance($instance);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): WorkerStatus
    {
        return $this->status;
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function getProxy(): ?ProxyInterface
    {
        return $this->proxy;
    }

    /**
     * @inheritDoc
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Set the worker group
     *
     * The worker group can be used to assign tasks to specific workers
     * by setting the same group on the worker and the task.
     *
     * @param string|null $group
     * @return $this
     */
    public function setGroup(?string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Set the proxy
     *
     * The taskmaster will get the proxy, start and update it when necessary.
     *
     *
     * @param ProxyInterface|null $proxy
     * @return $this
     */
    public function setProxy(?ProxyInterface $proxy): static
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * Remove the current instance when cloning the worker
     *
     * @return void
     */
    public function __clone(): void
    {
        $this->instance = null;
    }

    /**
     * Create a new worker instance
     *
     * @return WorkerInstanceInterface
     */
    abstract protected function createInstance(): WorkerInstanceInterface;
}