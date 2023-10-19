<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Request\WorkerDiedRequest;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Throwable;

/**
 * Class ProxyableSocketWorkerInstance
 *
 * A worker instance that can be proxied.
 * The instance has to use a socket and run asynchronously.
 * The proxy starts the instance and sets the socket to a {@link ProxiedSocket}.
 * The id obtained from {@link ProxyableWorkerInstanceInterface::getId()} is used to identify the instance
 * through the proxy and has to be unique.
 *
 * @package Aternos\Taskmaster\Worker\Instance
 */
abstract class ProxyableSocketWorkerInstance extends SocketWorkerInstance implements ProxyableWorkerInstanceInterface
{
    protected string $id;

    /**
     * @param TaskmasterOptions $options
     */
    public function __construct(TaskmasterOptions $options)
    {
        parent::__construct($options);
        $this->id = uniqid();
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function init(): static
    {
        $this->registerRequestHandler(WorkerDiedRequest::class, $this->handleWorkerDiedRequest(...));
        return parent::init();
    }

    /**
     * @inheritDoc
     */
    public function setStatus(WorkerInstanceStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSocket(SocketInterface $socket): static
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * Handle a worker died request
     *
     * This is sent by the proxy when the worker instance has died.
     *
     * @param WorkerDiedRequest $request
     * @return void
     * @throws Throwable
     */
    protected function handleWorkerDiedRequest(WorkerDiedRequest $request): void
    {
        $this->handleFail($request->getReason());
    }
}