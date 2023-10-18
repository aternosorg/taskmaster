<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\Request\RuntimeReadyRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\Response\TaskResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Exception;
use Fiber;
use Throwable;

/**
 * Class Runtime
 *
 * A runtime is the environment in which tasks are executed.
 * It receives {@link RunTaskRequest}s and executes those tasks.
 *
 * @package Aternos\Taskmaster\Runtime
 */
abstract class Runtime implements RuntimeInterface
{
    use RequestHandlingTrait;

    protected ?RunTaskRequest $currentTaskRequest = null;

    /**
     * Runtime constructor.
     */
    public function __construct()
    {
        $this->registerRequestHandler(RunTaskRequest::class, $this->runTask(...));
        $this->registerAfterRequestHandler(RunTaskRequest::class, $this->setReady(...));
    }

    /**
     * Update the runtime, e.g. by reading from the socket and handling requests
     *
     * @return static
     */
    abstract protected function update(): static;

    /**
     * Set the runtime to ready state by sending a {@link RuntimeReadyRequest}
     *
     * The worker will then send the next {@link RunTaskRequest}.
     *
     * @return void
     */
    protected function setReady(): void
    {
        $this->sendRequest(new RuntimeReadyRequest());
    }

    /**
     * Run a task
     *
     * The task is executed in a fiber to allow usage of the {@link Promise::wait()} method.
     * Exceptions are caught and sent back to the worker as {@link ExceptionResponse}.
     *
     * @throws Throwable
     */
    protected function runTask(RunTaskRequest $request): ResponseInterface
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
            return (new ExceptionResponse($request->getRequestId(), $exception))->loadFromTask($request->task);
        }
        return (new TaskResponse($request->getRequestId(), $result))->loadFromTask($request->task);
    }
}