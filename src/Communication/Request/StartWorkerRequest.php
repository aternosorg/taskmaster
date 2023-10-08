<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;
use Aternos\Taskmaster\Worker\ProxyableWorkerInterface;

class StartWorkerRequest extends Request
{
    public function __construct(
        protected ProxyableWorkerInterface $worker
    )
    {
        parent::__construct();
    }

    public function getWorker(): ProxyableWorkerInterface
    {
        return $this->worker;
    }
}