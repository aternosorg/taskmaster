<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\MessageInterface;

/**
 * Interface TaskMessageInterface
 *
 * This interface is used to mark a message class that contains synchronized properties and can be used to
 * synchronize these properties between the parent and the child process.
 *
 * @package Aternos\Taskmaster\Task
 */
interface TaskMessageInterface extends MessageInterface
{
    /**
     * Apply the synchronized properties from this message to the task
     *
     * @param TaskInterface $task
     * @return $this
     */
    public function applyToTask(TaskInterface $task): static;

    /**
     * Load the synchronized properties from the task to this message
     *
     * @param TaskInterface $task
     * @return $this
     */
    public function loadFromTask(TaskInterface $task): static;
}