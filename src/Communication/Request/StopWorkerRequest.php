<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;

class StopWorkerRequest extends Request
{
    public function __construct(protected string $workerId)
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getWorkerId(): string
    {
        return $this->workerId;
    }
}