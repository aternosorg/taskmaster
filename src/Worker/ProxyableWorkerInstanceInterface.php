<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;

interface ProxyableWorkerInstanceInterface extends WorkerInstanceInterface
{
    public function getId(): string;

    public function getSocket(): SocketInterface;

    public function setSocket(SocketInterface $socket): static;

    public function setStatus(WorkerStatus $status): static;

    public function hasDied(): bool;
}