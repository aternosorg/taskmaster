<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Runtime\RuntimeInterface;
use Aternos\Taskmaster\Task\TaskFactoryInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;

/**
 * Interface WorkerInterface
 *
 * A worker runs tasks in a {@link RuntimeInterface}.
 * It starts a {@link WorkerInstanceInterface} when necessary and assigns tasks to it.
 * When the worker instance dies, the worker will start a new one.
 * The worker can use a {@link ProxyInterface} to run the worker instance.
 *
 * @package Aternos\Taskmaster\Worker
 */
interface WorkerInterface
{
    /**
     * Get the worker group
     *
     * The worker group can be used to assign tasks to specific workers
     * by setting the same group on the worker and the task.
     *
     * @return string|null
     */
    public function getGroup(): ?string;

    /**
     * Get the current proxy
     *
     * The taskmaster will get the proxy, start and update it when necessary.
     *
     * @return ProxyInterface|null
     */
    public function getProxy(): ?ProxyInterface;

    /**
     * Set the taskmaster options
     *
     * If the options are not set, the taskmaster will set the default options.
     *
     * @param TaskmasterOptions $options
     * @return $this
     */
    public function setOptions(TaskmasterOptions $options): static;

    /**
     * Set the taskmaster options if necessary
     *
     * If the options are already set, they will not be overwritten.
     * This is called by {@link Taskmaster::addWorker()}.
     * If you want to set different options, e.g. a different PHP binary, call this before adding the worker.
     *
     * @param TaskmasterOptions $options
     * @return $this
     */
    public function setOptionsIfNecessary(TaskmasterOptions $options): static;

    /**
     * Set the init task factory for the worker
     *
     * Init tasks are executed once as first task on every worker instance to initialize the worker.
     *
     * @param TaskFactoryInterface|null $initTaskFactory
     * @return $this
     */
    public function setInitTaskFactory(?TaskFactoryInterface $initTaskFactory): static;

    /**
     * Set the init task factory for the worker if necessary
     *
     * If the init task factory is already set, it will not be overwritten.
     * This is called by {@link Taskmaster::addWorker()}.
     * If you want to set a different init task factory, call this before adding the worker.
     *
     * @param TaskFactoryInterface|null $initTaskFactory
     * @return $this
     */
    public function setInitTaskFactoryIfNecessary(?TaskFactoryInterface $initTaskFactory): static;

    /**
     * Update the worker and its instance
     *
     * @return $this
     */
    public function update(): static;

    /**
     * Stop the worker and its instance
     *
     * @return $this
     */
    public function stop(): static;

    /**
     * Get the worker status
     *
     * @return WorkerStatus
     * @see WorkerStatus
     */
    public function getStatus(): WorkerStatus;

    /**
     * Assign a task to the worker
     *
     * This doesn't mean that the task will be executed immediately, because the instance might need to be
     * started first. But the worker should change its status to {@link WorkerStatus::WORKING} immediately
     * to avoid getting assigned more tasks.
     *
     * @param TaskInterface $task
     * @return $this
     */
    public function assignTask(TaskInterface $task): static;
}