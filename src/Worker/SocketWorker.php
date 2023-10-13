<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Worker\Instance\SocketWorkerInstanceInterface;

abstract class SocketWorker extends Worker implements SocketWorkerInterface
{
    public function getSocket(): ?SocketInterface
    {
        if (!$this->instance instanceof SocketWorkerInstanceInterface) {
            return null;
        }
        return $this->instance->getSocket();
    }
}