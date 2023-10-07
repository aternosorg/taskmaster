<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Communication\ResponsePromise;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerStatus;

class SyncWorker extends Worker
{
    protected SyncRuntime $runtime;

    public function __construct()
    {
        parent::__construct();
        $this->runtime = new SyncRuntime($this);
    }

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
}