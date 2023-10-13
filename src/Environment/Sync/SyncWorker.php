<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Worker;

class SyncWorker extends Worker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new SyncWorkerInstance($this->taskmaster->getOptions());
    }
}