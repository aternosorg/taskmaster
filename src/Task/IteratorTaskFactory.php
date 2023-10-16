<?php

namespace Aternos\Taskmaster\Task;

use Iterator;

class IteratorTaskFactory extends TaskFactory
{
    /**
     * @param Iterator $iterator
     * @param string $taskClass
     */
    public function __construct(
        protected Iterator $iterator,
        protected string    $taskClass
    )
    {
    }

    /**
     * @param string|null $group
     * @return TaskInterface|null
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
     * @param mixed $iteratorValue
     * @return TaskInterface
     */
    protected function createTask(mixed $iteratorValue): TaskInterface
    {
        return new $this->taskClass($iteratorValue);
    }
}