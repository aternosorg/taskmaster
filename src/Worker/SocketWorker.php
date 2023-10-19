<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Worker\Instance\SocketWorkerInstanceInterface;

/**
 * Class SocketWorker
 *
 * A worker that uses a {@link SocketInterface} to communicate with its worker instance.
 *
 * @package Aternos\Taskmaster\Worker
 */
abstract class SocketWorker extends Worker implements SocketWorkerInterface
{
    /**
     * @inheritDoc
     */
    public function getSocket(): ?SocketInterface
    {
        if (!$this->instance instanceof SocketWorkerInstanceInterface) {
            return null;
        }
        return $this->instance->getSocket();
    }
}