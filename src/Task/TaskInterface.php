<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\RuntimeInterface;

interface TaskInterface
{
    public function run(): TaskResult;

    public function setRuntime(RuntimeInterface $runtime): static;
}