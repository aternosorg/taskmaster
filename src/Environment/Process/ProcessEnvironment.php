<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Environment\Environment;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ProcessEnvironment extends Environment
{

    public function createWorker(): WorkerInterface
    {
        return new ProcessWorker($this->options);
    }
}