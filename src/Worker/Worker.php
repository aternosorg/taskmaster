<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\TaskmasterOptions;

abstract class Worker implements WorkerInterface
{
    use RequestHandlingTrait;

    protected WorkerStatus $status = WorkerStatus::STARTING;
    protected ?TaskInterface $currentTask = null;

    public function __construct(protected TaskmasterOptions $options)
    {
    }

    public function init(): void
    {
        $this->registerRequestHandler(ExecuteFunctionRequest::class, $this->handleExecuteFunctionRequest(...));
    }

    /**
     * @param ExecuteFunctionRequest $request
     * @return mixed
     */
    protected function handleExecuteFunctionRequest(ExecuteFunctionRequest $request): mixed
    {
        $function = $request->getFunction();
        $arguments = $request->getArguments();
        return $this->currentTask->$function(...$arguments);
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
        $promise->then(function (ResponseInterface $response) {
            $this->status = WorkerStatus::IDLE;
            $this->currentTask->handleResult($response->getData());
            $this->currentTask = null;
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
}