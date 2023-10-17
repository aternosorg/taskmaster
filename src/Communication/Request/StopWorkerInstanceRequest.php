<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;

/**
 * Class StopWorkerInstanceRequest
 *
 * Sent from the proxy to the proxy runtime to stop a worker instance
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
class StopWorkerInstanceRequest extends Request
{
    /**
     * @param string $workerId
     */
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