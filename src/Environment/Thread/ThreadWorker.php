<?php

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\SocketWorker;

class ThreadWorker extends SocketWorker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new ThreadWorkerInstance($this->taskmaster->getOptions());
    }
}