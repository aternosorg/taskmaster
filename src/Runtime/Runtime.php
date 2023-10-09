<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Exception;
use Fiber;
use Throwable;

abstract class Runtime implements RuntimeInterface
{
    use RequestHandlingTrait;

    public function __construct()
    {
        $this->registerRequestHandler(RunTaskRequest::class, $this->runTask(...));
    }

    abstract protected function update(): void;

    /**
     * @throws Throwable
     */
    protected function runTask(RunTaskRequest $request): mixed
    {
        $request->task->setRuntime($this);
        $fiber = new Fiber($request->task->run(...));
        try {
            $fiber->start();
            while (!$fiber->isTerminated()) {
                $this->update();
            }
            return $fiber->getReturn();
        } catch (Exception $exception) {
            return new ExceptionResponse($request->getRequestId(), $exception);
        }
    }
}