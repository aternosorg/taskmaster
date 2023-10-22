<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Communication\Response;
use Aternos\Taskmaster\Task\TaskMessageInterface;
use Aternos\Taskmaster\Task\TaskMessageTrait;

/**
 * Class TaskResponse
 *
 * Task responses include synchronized fields using {@link TaskMessageTrait}
 *
 * @package Aternos\Taskmaster\Communication\Response
 */
class TaskResponse extends Response implements TaskMessageInterface
{
    use TaskMessageTrait;
}