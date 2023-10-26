<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\TaskFactory;
use Aternos\Taskmaster\Task\TaskInterface;

class EmptyTaskFactory extends TaskFactory
{
    protected int $current = 0;

    public function __construct(protected int $total)
    {
    }

    /**
     * @inheritDoc
     */
    public function createNextTask(?string $group): ?TaskInterface
    {
        if ($this->current >= $this->total) {
            return null;
        }
        $this->current++;
        return new EmptyTask();
    }
}