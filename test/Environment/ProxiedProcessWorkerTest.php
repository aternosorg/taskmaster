<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ProxiedProcessWorkerTest extends ProcessWorkerTest
{
    protected function createWorker(): WorkerInterface
    {
        return parent::createWorker()->setProxy(new ProcessProxy());
    }
}