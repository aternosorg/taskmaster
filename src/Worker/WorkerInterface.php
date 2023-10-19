<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Proxy\ProxyInterface;
use Aternos\Taskmaster\Runtime\RuntimeInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
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
     * Set the taskmaster instance
     *
     * The taskmaster will automatically set itself when adding the worker.
     *
     * @param Taskmaster $taskmaster
     * @return $this
     */
    public function setTaskmaster(Taskmaster $taskmaster): static;

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