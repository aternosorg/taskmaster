<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ProxiedProcessWorkerTest extends ProcessWorkerTest
{
    use ProxiedWorkerTestTrait;

    protected function createWorker(): WorkerInterface
    {
        return parent::createWorker()->setProxy(new ProcessProxy());
    }
}