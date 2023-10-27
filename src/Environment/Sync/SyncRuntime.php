<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Runtime\Runtime;
use Aternos\Taskmaster\Runtime\RuntimeInterface;
use Throwable;

/**
 * Class SyncRuntime
 *
 * The SyncRuntime is a {@link RuntimeInterface} implementation that runs in the same thread/process as the worker.
 * It communicates directly with the worker instance and does not use any kind of IPC.
 *
 * @package Aternos\Taskmaster\Environment\Sync
 */
class SyncRuntime extends Runtime
{
    /**
     * @param SyncWorkerInstance $workerInstance
     */
    public function __construct(protected SyncWorkerInstance $workerInstance)
    {
        $this->setReady();
        parent::__construct();
    }

    /**
     * Receive a request from the worker instance and handle it directly.
     *
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    public function receiveRequest(RequestInterface $request): ?ResponseInterface
    {
        $result = $this->handleRequest($request);
        $this->handleAfterRequest($request);
        return $result;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        $response = $this->workerInstance->receiveRequest($request);
        return (new ResponsePromise())->resolve($response);
    }

    /**
     * @inheritDoc
     */
    protected function runTask(RunTaskRequest $request): ResponseInterface
    {
        $request->getTask()->setSync();
        return parent::runTask($request);
    }

    /**
     * @inheritDoc
     */
    protected function update(): static
    {
        return $this;
    }
}