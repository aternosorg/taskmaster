<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\Request\RuntimeReadyRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\Response\TaskResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Exception\WorkerFailedException;
use Aternos\Taskmaster\Task\Synchronized;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Task\TaskMessageInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Exception;
use Throwable;

/**
 * Class WorkerInstance
 *
 * Basic worker instance implementation.
 * A worker instance represents a single worker, e.g. a process or thread and is
 * wrapped by a worker which always holds one or no instance.
 * When a worker instance dies or is stopped, the worker creates a new worker instance.
 *
 * @see WorkerInstanceInterface
 * @package Aternos\Taskmaster\Worker\Instance
 */
abstract class WorkerInstance implements WorkerInstanceInterface
{
    use RequestHandlingTrait;

    protected WorkerInstanceStatus $status = WorkerInstanceStatus::CREATED;
    protected ?TaskInterface $currentTask = null;
    protected ?ResponsePromise $currentResponsePromise = null;

    /**
     * @param TaskmasterOptions $options
     */
    public function __construct(protected TaskmasterOptions $options)
    {
    }

    /**
     * @inheritDoc
     */
    public function init(): static
    {
        $this->registerRequestHandler(ExecuteFunctionRequest::class, $this->handleExecuteFunctionRequest(...));
        $this->registerRequestHandler(RuntimeReadyRequest::class, $this->handleRuntimeReadyRequest(...));
        return $this;
    }

    /**
     * Handle a {@link RuntimeReadyRequest} to set the worker instance status to {@link WorkerInstanceStatus::IDLE}.
     *
     * @return void
     */
    protected function handleRuntimeReadyRequest(): void
    {
        $this->status = WorkerInstanceStatus::IDLE;
    }

    /**
     * Handle an {@link ExecuteFunctionRequest} to execute a function on the current task.
     *
     * Applies {@link Synchronized} fields to the task before executing the function and adds them
     * back to the response after execution.
     * Also catches exceptions and returns an {@link ExceptionResponse} to the child instead.
     *
     * @param ExecuteFunctionRequest $request
     * @return ResponseInterface
     */
    protected function handleExecuteFunctionRequest(ExecuteFunctionRequest $request): ResponseInterface
    {
        $function = $request->getFunction();
        $arguments = $request->getArguments();
        try {
            $request->applyToTask($this->currentTask);
            $result = $this->currentTask->$function(...$arguments);
            return (new TaskResponse($request->getRequestId(), $result))->loadFromTask($this->currentTask);
        } catch (Exception $exception) {
            return (new ExceptionResponse($request->getRequestId(), $exception))->loadFromTask($this->currentTask);
        }
    }

    /**
     * @inheritDoc
     */
    public function runTask(TaskInterface $task): ResponsePromise
    {
        $this->status = WorkerInstanceStatus::WORKING;
        $this->currentTask = $task;
        return $this->sendRunTaskRequest(new RunTaskRequest($task));
    }

    /**
     * Send a {@link RunTaskRequest} to the worker instance
     *
     * Returns a {@link ResponsePromise} which can be resolved asynchronously.
     * Gets {@link Synchronized} fields from the task response and applies them to the task.
     *
     * @param RunTaskRequest $request
     * @return ResponsePromise
     */
    protected function sendRunTaskRequest(RunTaskRequest $request): ResponsePromise
    {
        $promise = $this->sendRequest($request);
        $this->currentResponsePromise = $promise;
        $promise->then(function (ResponseInterface $response) {
            if ($response instanceof TaskMessageInterface) {
                $response->applyToTask($this->currentTask);
            }
            $this->currentTask->handleResult($response->getData());
            $this->currentTask = null;
            $this->currentResponsePromise = null;
        })->catch(function (Exception $exception, ResponseInterface $response) {
            if ($response instanceof TaskMessageInterface) {
                $response->applyToTask($this->currentTask);
            }
            $this->currentTask->handleError($exception);
            $this->currentTask = null;
            $this->currentResponsePromise = null;
        });
        return $promise;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): WorkerInstanceStatus
    {
        return $this->status;
    }


    /**
     * Handle a fail of the worker instance
     *
     * If necessary, it resolves the current task response promise with a {@link WorkerFailedException}.
     *
     * @param string|null $reason
     * @return $this
     * @throws Throwable
     */
    public function handleFail(?string $reason = null): static
    {
        $this->status = WorkerInstanceStatus::FAILED;
        $response = new ExceptionResponse($reason, new WorkerFailedException($reason));
        $this->currentResponsePromise?->resolve($response);
        $this->stop();
        return $this;
    }
}