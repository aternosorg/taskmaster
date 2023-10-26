<?php

namespace Aternos\Taskmaster\Test\Integration;

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