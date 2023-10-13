<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\SocketWorker;

class ForkWorker extends SocketWorker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new ForkWorkerInstance($this->taskmaster->getOptions());
    }
}