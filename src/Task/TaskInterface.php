<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Response\ErrorResponse;
use Aternos\Taskmaster\Runtime\RuntimeInterface;

interface TaskInterface
{
    public function run(): mixed;

    public function handleResult(mixed $result): void;

    public function handleError(ErrorResponse $error): void;

    public function setRuntime(RuntimeInterface $runtime): static;
}