<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;

/**
 * Interface ProxyInterface
 *
 * A proxy can be used to start workers using a different environment, e.g. CLI as base environment.
 *
 * @package Aternos\Taskmaster\Proxy
 */
interface ProxyInterface
{
    /**
     * Set the global taskmaster options
     *
     * This is called by {@link Taskmaster::addWorker()} if the proxy isn't running yet.
     * You can set different options, e.g. a different PHP binary and then start your proxy
     * before passing it through the worker to {@link Taskmaster::addWorker()}.
     *
     * @param TaskmasterOptions $options
     * @return $this
     */
    public function setOptions(TaskmasterOptions $options): static;

    /**
     * Check if the proxy is running
     *
     * The proxy will be started using {@link ProxyInterface::start()} if it's passed to the {@link Taskmaster}
     * and is not running yet.
     *
     * @return bool
     */
    public function isRunning(): bool;

    /**
     * Start the proxy
     *
     * @return $this
     */
    public function start(): static;

    /**
     * Stop the proxy
     *
     * @return $this
     */
    public function stop(): static;

    /**
     * Update the proxy, e.g. by reading the socket
     *
     * @return $this
     */
    public function update(): static;

    /**
     * Start a worker instance on the proxy
     *
     * The instance can be serialized and then started on a different process.
     *
     * @param ProxyableWorkerInstanceInterface $worker
     * @return ResponsePromise
     */
    public function startWorkerInstance(ProxyableWorkerInstanceInterface $worker): ResponsePromise;

    /**
     * Stop a worker instance on the proxy
     *
     * This instance cannot be serialized anymore, but {@link ProxyableWorkerInstanceInterface::getId()} can be used
     * to identify the instance on the proxy.
     *
     * @param ProxyableWorkerInstanceInterface $worker
     * @return ResponsePromise
     */
    public function stopWorkerInstance(ProxyableWorkerInstanceInterface $worker): ResponsePromise;

    /**
     * Get the socket that the socket uses to communicate
     *
     * @return SocketInterface|null
     */
    public function getSocket(): ?SocketInterface;

    /**
     * Get the wrapped proxy socket, that can be used to create a {@link ProxiedSocket}.
     *
     * @return ProxySocketInterface|null
     */
    public function getProxySocket(): ?ProxySocketInterface;
}