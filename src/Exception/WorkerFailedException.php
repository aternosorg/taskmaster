<?php

namespace Aternos\Taskmaster\Exception;

use Throwable;

/**
 * Class WorkerFailedException
 *
 * This exception is thrown when a worker fails during request execution.
 * This might not be due to the task itself, so retrying the task could work.
 *
 * @package Aternos\Taskmaster\Exception
 */
class WorkerFailedException extends TaskmasterException
{
    /**
     * @param string $message
     */
    public function __construct(string $message = "")
    {
        $message = "Worker failed: " . $message;
        parent::__construct($message);
    }
}