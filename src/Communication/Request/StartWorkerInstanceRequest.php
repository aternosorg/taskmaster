<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;

/**
 * Class StartWorkerRequest
 *
 * Sent from the proxy to the proxy runtime to start a worker instance
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
class StartWorkerInstanceRequest extends Request
{
    /**
     * @param ProxyableWorkerInstanceInterface $worker
     */
    public function __construct(protected ProxyableWorkerInstanceInterface $worker)
    {
        parent::__construct();
    }

    /**
     * @return ProxyableWorkerInstanceInterface
     */
    public function getWorker(): ProxyableWorkerInstanceInterface
    {
        return $this->worker;
    }
}