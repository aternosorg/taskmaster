<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Communication\Response\ErrorResponse;

class WorkerFailedResponse extends ErrorResponse
{
    public function __construct(?string $reason = null)
    {
        parent::__construct("", $reason);
    }

    /**
     * @return string
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