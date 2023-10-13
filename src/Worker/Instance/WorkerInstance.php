<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\Request\RuntimeReadyRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Response\ErrorResponse;
use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\Response\WorkerFailedResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\WorkerStatus;
use Throwable;

abstract class WorkerInstance implements WorkerInstanceInterface
{
    use RequestHandlingTrait;

    protected WorkerStatus $status = WorkerStatus::CREATED;
    protected ?TaskInterface $currentTask = null;
    protected ?ResponsePromise $currentResponsePromise = null;

    public function __construct(protected TaskmasterOptions $options)
    {
    }

    public function init(): static
    {
        $this->registerRequestHandler(ExecuteFunctionRequest::class, $this->handleExecuteFunctionRequest(...));
        $this->registerRequestHandler(RuntimeReadyRequest::class, $this->handleRuntimeReadyRequest(...));
        return $this;
    }

    /**
     * @param RuntimeReadyRequest $request
     * @return void
     */
    protected function handleRuntimeReadyRequest(RuntimeReadyRequest $request): void
    {
        $this->status = WorkerStatus::IDLE;
    }

    /**
     * @param ExecuteFunctionRequest $request
     * @return mixed
     */
    protected function handleExecuteFunctionRequest(ExecuteFunctionRequest $request): mixed
    {
        $function = $request->getFunction();
        $arguments = $request->getArguments();
        try {
            return $this->currentTask->$function(...$arguments);
        } catch (\Exception $exception) {
            return new ExceptionResponse($request->getRequestId(), $exception);
        }
    }

    /**
     * @param TaskInterface $task
     * @return ResponsePromise
     */
    public function runTask(TaskInterface $task): ResponsePromise
    {
        $this->status = WorkerStatus::WORKING;
        $this->currentTask = $task;
        return $this->sendRunTaskRequest(new RunTaskRequest($task));
    }

    /**
     * @param RunTaskRequest $request
     * @return ResponsePromise
     */
    protected function sendRunTaskRequest(RunTaskRequest $request): ResponsePromise
    {
        $promise = $this->sendRequest($request);
        $this->currentResponsePromise = $promise;
        $promise->then(function (ResponseInterface $response) {
            if ($response instanceof ErrorResponse) {
                $this->currentTask->handleError($response);
            } else {
                $this->currentTask->handleResult($response->getData());
            }
            $this->currentTask = null;
            $this->currentResponsePromise = null;
        })->catch(function (\Exception $exception) {
            $this->currentTask->handleError(new ExceptionResponse(0, $exception));
            $this->currentTask = null;
            $this->currentResponsePromise = null;
        });
        return $promise;
    }

    /**
     * @return WorkerStatus
     */
    public function getStatus(): WorkerStatus
    {
        return $this->status;
    }

    /**
     * @param string|null $reason
     * @return $this
     * @throws Throwable
     */
    protected function handleFail(?string $reason = null): static
    {
        $this->status = WorkerStatus::FAILED;
        $this->currentResponsePromise?->resolve(new WorkerFailedResponse($reason));
        //$this->stop();
        return $this;
    }
}