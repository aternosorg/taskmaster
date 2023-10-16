<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Worker\WorkerInstanceStatus;

interface ProxyableWorkerInstanceInterface extends SocketWorkerInstanceInterface
{
    public function getId(): string;

    public function setStatus(WorkerInstanceStatus $status): static;

    public function hasDied(): bool;
}