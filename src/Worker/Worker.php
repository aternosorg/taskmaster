<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Task\TaskInterface;

abstract class Worker implements WorkerInterface
{
    use RequestHandlingTrait;

    protected WorkerStatus $status;
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

    public function runTask(TaskInterface $task): void
    {
        $this->status = WorkerStatus::WORKING;
        $this->currentTask = $task;
        $this->sendRequest(new RunTaskRequest($task));
    }

    /**
     * @return WorkerStatus
     */
    public function getStatus(): WorkerStatus
    {
        return $this->status;
    }
}