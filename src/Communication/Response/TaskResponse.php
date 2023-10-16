<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Communication\Response;
use Aternos\Taskmaster\Task\TaskMessageInterface;
use Aternos\Taskmaster\Task\TaskMessageTrait;

class TaskResponse extends Response implements TaskMessageInterface
{
    use TaskMessageTrait;
}