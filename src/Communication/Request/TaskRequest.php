<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Task\TaskMessageInterface;
use Aternos\Taskmaster\Task\TaskMessageTrait;

abstract class TaskRequest extends Request implements TaskMessageInterface
{
    use TaskMessageTrait;
}