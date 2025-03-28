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

    public function testChildDestructMultipleTasks(): void
    {
        // This test does not work with the sync worker yet
        // because the child and parent process are the same

        // It's probably not a huge issue, but could be looked at
        // again in the future
        $this->markTestSkipped();
    }

    public function testSyncTask(): void
    {
        $task = new SyncTask();
        $this->taskmaster->runTask($task);
        $this->taskmaster->wait();
        $this->assertTrue($task->getResult());
    }
}