<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerStatus;
use Throwable;

class SyncWorker extends Worker
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

    public function stop(): void
    {
    }

    public function update(): void
    {
    }

    /**
     * @return Promise
     * @throws Throwable
     */
    public function start(): Promise
    {
        $this->runtime = new SyncRuntime($this);
        $this->status = WorkerStatus::IDLE;
        return (new Promise())->resolve();
    }
}