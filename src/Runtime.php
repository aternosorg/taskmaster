<?php

namespace Aternos\Taskmaster;

use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
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
    protected function runTask(RunTaskRequest $request): void
    {
        $request->task->setRuntime($this);
        $fiber = new Fiber($request->task->run(...));
        $fiber->start();
        while (!$fiber->isTerminated()) {
            $this->update();
        }
    }
}