<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstance;
use Aternos\Taskmaster\Worker\WorkerStatus;
use Throwable;

class SyncWorkerInstance extends WorkerInstance
{
    protected SyncRuntime $runtime;

    public function receiveRequest(RequestInterface $request): ?ResponseInterface
    {
        return $this->handleRequest($request);
    }

    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        $response = $this->runtime->receiveRequest($request);
        return (new ResponsePromise())->resolve($response);
    }

    public function stop(): static
    {
        return $this;
    }

    public function update(): static
    {
        return $this;
    }

    /**
     * @return Promise
     * @throws Throwable
     */
    public function start(): Promise
    {
        $this->status = WorkerStatus::STARTING;
        $this->runtime = new SyncRuntime($this);
        return (new Promise())->resolve();
    }
}