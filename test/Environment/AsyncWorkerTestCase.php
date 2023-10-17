<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\WorkerInterface;

abstract class AsyncWorkerTestCase extends WorkerTestCase
{
    abstract protected function createWorker(): WorkerInterface;

    protected function createTaskmaster(): void
    {
        $this->taskmaster = new Taskmaster();
        $this->taskmaster->addWorker($this->createWorker(), 3);
    }
}