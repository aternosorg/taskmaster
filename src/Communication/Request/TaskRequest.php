<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Task\TaskMessageInterface;
use Aternos\Taskmaster\Task\TaskMessageTrait;

/**
 * Class TaskRequest
 *
 * Parent class for all task requests that include synchronized fields using {@link TaskMessageTrait}
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
abstract class TaskRequest extends Request implements TaskMessageInterface
{
    use TaskMessageTrait;
}