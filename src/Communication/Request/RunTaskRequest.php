<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Task\Task;
use Aternos\Taskmaster\Task\TaskInterface;

/**
 * Class RunTaskRequest
 *
 * Sent from the worker to the runtime to run a task
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
class RunTaskRequest extends Request
{
    /**
     * @param Task $task
     */
    public function __construct(public TaskInterface $task)
    {
        parent::__construct();
    }

    /**
     * @return TaskInterface
     */
    public function getTask(): TaskInterface
    {
        return $this->task;
    }
}