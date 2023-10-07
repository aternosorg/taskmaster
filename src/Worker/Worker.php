<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Communication\ResponsePromise;
use Aternos\Taskmaster\Task\TaskInterface;

abstract class Worker implements WorkerInterface
{
    use RequestHandlingTrait;

    protected WorkerStatus $status = WorkerStatus::IDLE;
    protected ?TaskInterface $currentTask = null;

    public function __construct()
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
        $promise = $this->sendRequest(new RunTaskRequest($task));
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