<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Task\Task;

class RunTaskRequest extends Request
{
    public function __construct(public Task $task)
    {
        parent::__construct();
    }
}