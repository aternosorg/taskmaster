<?php

namespace Aternos\Taskmaster\Communication\Response;

/**
 * Class WorkerFailedResponse
 *
 * Error response for a worker that failed during request execution.
 * This might not be due to the task itself, so retrying the task could work.
 *
 * @package Aternos\Taskmaster\Communication\Response
 */
class WorkerFailedResponse extends ErrorResponse
{
    public function __construct(?string $reason = null)
    {
        parent::__construct("", $reason);
    }

    /**
     * @inheritDoc
     */
    public function getError(): string
    {
        $error = "Worker failed";
        if ($this->data) {
            $error .= ": " . $this->data;
        } else {
            $error .= ".";
        }
        return $error;
    }
}