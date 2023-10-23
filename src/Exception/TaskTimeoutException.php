<?php

namespace Aternos\Taskmaster\Exception;

/**
 * Class TaskTimeoutException
 *
 * This exception is thrown when a task times out.
 *
 * @package Aternos\Taskmaster\Exception
 */
class TaskTimeoutException extends TaskmasterException
{
    /**
     * @param float $timeout
     * @param float $time
     */
    public function __construct(protected float $timeout, protected float $time)
    {
        parent::__construct("Task timed out after " . $this->time . " seconds (timeout: " . $this->timeout . " seconds).");
    }

    /**
     * Get the defined task timeout
     *
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * Get the actual task runtime
     *
     * @return float
     */
    public function getTime(): float
    {
        return $this->time;
    }
}