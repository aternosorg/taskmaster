<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Request\WorkerDiedRequest;
use Throwable;

abstract class ProxyableSocketWorkerInstance extends SocketWorkerInstance implements ProxyableWorkerInstanceInterface
{
    public function init(): static
    {
        $this->registerRequestHandler(WorkerDiedRequest::class, $this->handleWorkerDiedRequest(...));
        return parent::init();
    }

    /**
     * @param WorkerDiedRequest $request
     * @return void
     * @throws Throwable
     */
    protected function handleWorkerDiedRequest(WorkerDiedRequest $request): void
    {
        $this->handleFail($request->getReason());
    }
}