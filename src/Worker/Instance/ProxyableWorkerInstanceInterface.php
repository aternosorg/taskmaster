<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Proxy\ProxiedSocket;

/**
 * Interface ProxyableWorkerInstanceInterface
 *
 * A worker instance that can be proxied.
 * The instance has to use a socket and run asynchronously.
 * The proxy starts the instance and sets the socket to a {@link ProxiedSocket}.
 * The id obtained from {@link ProxyableWorkerInstanceInterface::getId()} is used to identify the instance
 * through the proxy and has to be unique.
 *
 * @package Aternos\Taskmaster\Worker\Instance
 */
interface ProxyableWorkerInstanceInterface extends SocketWorkerInstanceInterface
{
    /**
     * Get a unique id for this worker instance
     *
     * This id is used to identify the instance through the proxy.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set the status of the worker instance
     *
     * The status is forced to {@link WorkerInstanceStatus::STARTING} on the parent when the instance is started
     * on the proxy.
     *
     * @param WorkerInstanceStatus $status
     * @return $this
     */
    public function setStatus(WorkerInstanceStatus $status): static;

    /**
     * Set the socket of the worker instance
     *
     * This is used by the proxy to set the socket to a {@link ProxiedSocket}.
     *
     * @param SocketInterface $socket
     * @return $this
     */
    public function setSocket(SocketInterface $socket): static;
}