<?php

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerInstanceInterface;

class ThreadWorker extends Worker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new ThreadWorkerInstance($this->taskmaster->getOptions());
    }
}