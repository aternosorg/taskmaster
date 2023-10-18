<?php

namespace Aternos\Taskmaster\Task;

use Iterator;

/**
 * Class IteratorTaskFactory
 *
 * A {@link TaskFactory} that creates tasks from an iterator, e.g. a {@link \DirectoryIterator}
 *
 * @package Aternos\Taskmaster\Task
 */
class IteratorTaskFactory extends TaskFactory
{
    /**
     * @param Iterator $iterator
     * @param string $taskClass
     */
    public function __construct(
        protected Iterator $iterator,
        protected string   $taskClass
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function createNextTask(?string $group): ?TaskInterface
    {
        if (!$this->iterator->valid()) {
            return null;
        }

        $task = $this->createTask($this->iterator->current());
        $this->iterator->next();
        return $task;
    }

    /**
     * Create a task from the iterator value
     *
     * This can be overwritten to create a different task from the iterator value
     * or pass further arguments to the task constructor.
     *
     * @param mixed $iteratorValue
     * @return TaskInterface
     */
    protected function createTask(mixed $iteratorValue): TaskInterface
    {
        return new $this->taskClass($iteratorValue);
    }
}