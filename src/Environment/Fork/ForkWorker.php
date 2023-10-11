<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerInstanceInterface;

class ForkWorker extends Worker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new ForkWorkerInstance($this->taskmaster->getOptions());
    }
}