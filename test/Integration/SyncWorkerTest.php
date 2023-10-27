<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Environment\Sync\SyncWorker;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Util\Task\SyncTask;

class SyncWorkerTest extends WorkerTestCase
{
    protected function createTaskmaster(): void
    {
        $this->taskmaster = new Taskmaster();
        $this->taskmaster->addWorker(new SyncWorker());
    }

    public function testSyncTask(): void
    {
        $task = new SyncTask();
        $this->taskmaster->runTask($task);
        $this->taskmaster->wait();
        $this->assertTrue($task->getResult());
    }
}