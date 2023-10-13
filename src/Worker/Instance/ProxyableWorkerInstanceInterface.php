<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Worker\WorkerStatus;

interface ProxyableWorkerInstanceInterface extends SocketWorkerInstanceInterface
{
    public function getId(): string;

    public function setStatus(WorkerStatus $status): static;

    public function hasDied(): bool;
}