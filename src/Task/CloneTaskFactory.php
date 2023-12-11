<?php

namespace Aternos\Taskmaster\Task;

/**
 * Class CloneTaskFactory
 *
 * The CloneTaskFactory creates clones of a task.
 *
 * @package Aternos\Taskmaster\Task
 */
class CloneTaskFactory extends TaskFactory
{
    protected int $count = 0;

    /**
     * @param TaskInterface $task
     * @param int|null $limit
     */
    public function __construct(protected TaskInterface $task, protected ?int $limit = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function createNextTask(?string $group): ?TaskInterface
    {
        if ($this->limit !== null && $this->count >= $this->limit) {
            return null;
        }
        $this->count++;
        return clone $this->task;
    }
}