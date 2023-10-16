<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\MessageInterface;

interface TaskMessageInterface extends MessageInterface
{
    public function applyToTask(TaskInterface $task): static;

    public function loadFromTask(TaskInterface $task): static;
}