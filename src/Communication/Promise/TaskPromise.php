<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Task\TaskInterface;
use Exception;
use Throwable;

/**
 * Class TaskPromise
 *
 * Promise implementation for tasks, resolves with the task result or
 * rejects with the task error. Success and exception handlers are called
 * with the task as second argument.
 *
 * @package Aternos\Taskmaster\Communication\Promise
 */
class TaskPromise extends Promise
{
    /**
     * @param TaskInterface $task
     * @param ResponsePromise|null $responsePromise
     * @throws Throwable
     */
    public function __construct(protected TaskInterface $task, ?ResponsePromise $responsePromise = null)
    {
        if ($responsePromise) {
            $this->setResponsePromise($responsePromise);
        }
    }

    /**
     * @param ResponsePromise $responsePromise
     * @return $this
     * @throws Throwable
     */
    public function setResponsePromise(ResponsePromise $responsePromise): static
    {
        $responsePromise->then(function ($response) {
            $this->resolve($response->getData());
        })->catch(function (Exception $exception) {
            $this->reject($exception);
        });
        return $this;
    }

    /**
     * @return TaskInterface
     */
    public function getTask(): TaskInterface
    {
        return $this->task;
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalRejectArguments(): array
    {
        return [$this->task];
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalResolveArguments(): array
    {
        return [$this->task];
    }
}