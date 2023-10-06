<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Environment\Environment;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ForkEnvironment extends Environment
{
    public function createWorker(): WorkerInterface
    {
        return new ForkWorker();
    }
}