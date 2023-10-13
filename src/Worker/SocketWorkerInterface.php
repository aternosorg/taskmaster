<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;

interface SocketWorkerInterface
{
    public function getSocket(): ?SocketInterface;
}