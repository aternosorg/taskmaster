<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;

/**
 * Interface SocketWorkerInterface
 *
 * A worker that uses a {@link SocketInterface} to communicate with its worker instance.
 *
 * @package Aternos\Taskmaster\Worker
 */
interface SocketWorkerInterface
{
    /**
     * Get the socket of the current worker instance
     *
     * @return SocketInterface|null
     */
    public function getSocket(): ?SocketInterface;
}