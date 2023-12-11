<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Task\TaskFactory;

/**
 * Class InstanceTaskFactory
 *
 * The InstanceTaskFactory creates instances of a task class.
 *
 * @package Aternos\Taskmaster\Task
 */
class InstanceTaskFactory extends TaskFactory
{
    protected int $count = 0;

    /**
     * @param class-string<TaskInterface> $taskClass
     * @param int|null $limit
     */
    public function __construct(protected string $taskClass, protected ?int $limit = null)
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
        return new $this->taskClass();
    }
}