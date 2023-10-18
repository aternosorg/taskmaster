<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Taskmaster;

/**
 * Interface TaskFactoryInterface
 *
 * A task factory creates tasks for the {@link Taskmaster}.
 * It can be used to create tasks for one or multiple specific groups.
 * Task factories are always called before the {@link Taskmaster} uses the task list from {@link Taskmaster::$tasks}.
 *
 * @see Taskmaster::getNextTask()
 * @package Aternos\Taskmaster\Task
 */
interface TaskFactoryInterface
{
    /**
     * Get the groups this task factory creates tasks for
     *
     * Only if a task for that group is requested, the task factory will be used.
     * If null is returned, the task factory will be used for all groups.
     * If the returned array contains null, the task factory will be used for tasks without a group.
     *
     * @return string[]|null[]|null
     * @see Taskmaster::getNextTask()
     */
    public function getGroups(): ?array;

    /**
     * Create the next task
     *
     * This is called whenever a worker is ready to run a task.
     * If null is returned, the next task factory or the task list will be used.
     * The {@link Taskmaster} will still call this method even after it returned null.
     *
     * @param string|null $group
     * @return TaskInterface|null
     * @see Taskmaster::getNextTask()
     */
    public function createNextTask(?string $group): ?TaskInterface;
}