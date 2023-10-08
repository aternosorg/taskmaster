<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Runtime\RuntimeInterface;

interface TaskInterface
{
    public function run(): mixed;

    public function handleResult(mixed $result): void;

    public function setRuntime(RuntimeInterface $runtime): static;
}