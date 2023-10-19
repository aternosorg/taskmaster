<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;

/**
 * Interface SocketWorkerInstanceInterface
 *
 * Interface for worker instances that use a socket for communication.
 *
 * @package Aternos\Taskmaster\Worker\Instance
 */
interface SocketWorkerInstanceInterface
{
    /**
     * Get the socket of the worker instance
     *
     * @return SocketInterface|null
     */
    public function getSocket(): ?SocketInterface;

    /**
     * Check if the worker instance has died
     *
     * A worker instance that was never born can not die.
     * Therefore, should not return true before the instance was started or the instance
     * would immediately be marked as failed and a new one would be created.
     *
     * @return bool
     */
    public function hasDied(): bool;
}