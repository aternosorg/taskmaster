<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Worker\ProxyableWorkerInstanceInterface;

class StartWorkerRequest extends Request
{
    public function __construct(
        protected ProxyableWorkerInstanceInterface $worker
    )
    {
        parent::__construct();
    }

    public function getWorker(): ProxyableWorkerInstanceInterface
    {
        return $this->worker;
    }
}