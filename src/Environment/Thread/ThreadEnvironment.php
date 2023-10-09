<?php

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Environment\Environment;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ThreadEnvironment extends Environment
{
    public function createWorker(): WorkerInterface
    {
        return new ThreadWorker($this->options);
    }
}