<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstance;
use Aternos\Taskmaster\Worker\WorkerInstanceStatus;
use Throwable;

class SyncWorkerInstance extends WorkerInstance
{
    protected SyncRuntime $runtime;

    public function receiveRequest(RequestInterface $request): ?ResponseInterface
    {
        $result = $this->handleRequest($request);
        $this->handleAfterRequest($request);
        return $result;
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
     * @return $this
     */
    public function start(): static
    {
        $this->status = WorkerInstanceStatus::STARTING;
        $this->runtime = new SyncRuntime($this);
        return $this;
    }
}