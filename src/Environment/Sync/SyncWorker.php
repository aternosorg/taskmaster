<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerInstanceInterface;

class SyncWorker extends Worker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new SyncWorkerInstance($this->taskmaster->getOptions());
    }
}