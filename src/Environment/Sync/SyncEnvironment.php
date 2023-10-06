<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Environment\Environment;
use Aternos\Taskmaster\Worker\WorkerInterface;

class SyncEnvironment extends Environment
{
    protected ?SyncWorker $worker = null;

    public function createWorker(): WorkerInterface
    {
        if ($this->worker === null) {
            $this->worker = new SyncWorker();
        }
        return $this->worker;
    }
}