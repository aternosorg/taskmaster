<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\Request\RuntimeReadyRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\Response\PhpErrorResponse;
use Exception;
use Fiber;
use Throwable;

abstract class Runtime implements RuntimeInterface
{
    use RequestHandlingTrait;

    protected ?RunTaskRequest $currentTaskRequest = null;

    public function __construct()
    {
        $this->registerRequestHandler(RunTaskRequest::class, $this->runTask(...));
    }

    abstract protected function update(): static;

    /**
     * @return void
     */
    protected function setReady(): void
    {
        $this->sendRequest(new RuntimeReadyRequest());
    }

    /**
     * @throws Throwable
     */
    protected function runTask(RunTaskRequest $request): mixed
    {
        $this->currentTaskRequest = $request;
        $request->task->setRuntime($this);
        $fiber = new Fiber($request->task->run(...));
        try {
            $fiber->start();
            while (!$fiber->isTerminated()) {
                $this->update();
            }
            $result = $fiber->getReturn();
        } catch (Exception $exception) {
            $result = new ExceptionResponse($request->getRequestId(), $exception);
        }
        $this->setReady();
        return $result;
    }
}