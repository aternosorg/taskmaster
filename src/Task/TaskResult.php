<?php

namespace Aternos\Taskmaster\Task;

class TaskResult
{
    protected bool $success = true;

    /**
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success): static
    {
        $this->success = $success;
        return $this;
    }
}