<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Task\Task;

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
    public function __construct(public Task $task)
    {
        parent::__construct();
    }
}