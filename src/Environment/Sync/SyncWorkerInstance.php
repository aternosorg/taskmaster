<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstance;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceStatus;
use Throwable;

/**
 * Class SyncWorkerInstance
 *
 * A {@link WorkerInstance} implementation that runs tasks synchronously in the same process, see {@link SyncWorker}.
 *
 * @package Aternos\Taskmaster\Environment\Sync
 */
class SyncWorkerInstance extends WorkerInstance
{
    protected SyncRuntime $runtime;

    /**
     * Receive a request and handle it
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
        $response = $this->runtime->receiveRequest($request);
        return (new ResponsePromise())->resolve($response);
    }

    /**
     * @inheritDoc
     */
    public function stop(): static
    {
        if ($this->status !== WorkerInstanceStatus::FAILED) {
            $this->status = WorkerInstanceStatus::FINISHED;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update(): static
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function start(): static
    {
        $this->status = WorkerInstanceStatus::STARTING;
        $this->runtime = new SyncRuntime($this);
        return $this;
    }
}