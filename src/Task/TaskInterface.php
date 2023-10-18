<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Response\ErrorResponse;
use Aternos\Taskmaster\Communication\Response\PhpError;
use Aternos\Taskmaster\Runtime\RuntimeInterface;

interface TaskInterface
{

    #[RunOnParent]
    public function handleResult(mixed $result): void;

    #[RunOnParent]
    public function handleError(ErrorResponse $error): void;

    #[RunOnParent]
    public function getGroup(): ?string;

    #[RunOnChild]
    public function run();

    #[RunOnChild]
    public function handleUncriticalError(PhpError $error): bool;

    #[RunOnChild]
    public function setRuntime(RuntimeInterface $runtime): static;
}