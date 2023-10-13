<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;

interface SocketWorkerInstanceInterface
{
    public function getSocket(): ?SocketInterface;

    public function setSocket(SocketInterface $socket): static;
}