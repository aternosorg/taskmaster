<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Runtime\Runtime;

class SyncRuntime extends Runtime
{

    public function __construct(protected SyncWorker $worker)
    {
        parent::__construct();
    }

    public function receiveRequest(RequestInterface $request): ?ResponseInterface
    {
        return $this->handleRequest($request);
    }

    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        $response = $this->worker->receiveRequest($request);
        return (new ResponsePromise())->resolve($response);
    }

    protected function update(): void
    {
    }
}