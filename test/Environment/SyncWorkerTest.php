<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Environment\Sync\SyncWorker;
use Aternos\Taskmaster\Taskmaster;

class SyncWorkerTest extends WorkerTestCase
{
    protected function createTaskmaster(): void
    {
        $this->taskmaster = new Taskmaster();
        $this->taskmaster->addWorker(new SyncWorker());
    }
}