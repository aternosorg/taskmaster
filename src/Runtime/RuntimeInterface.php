<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\CommunicatorInterface;
use Aternos\Taskmaster\Communication\Request\RunTaskRequest;

/**
 * Interface RuntimeInterface
 *
 * A runtime is the environment in which tasks are executed.
 * It receives {@link RunTaskRequest}s and executes those tasks.
 *
 * @package Aternos\Taskmaster\Runtime
 */
interface RuntimeInterface extends CommunicatorInterface
{
}