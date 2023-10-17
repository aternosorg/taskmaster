<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Environment\Process\ProcessWorker;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ProcessWorkerTest extends AsyncWorkerTestCase
{
    protected function createWorker(): WorkerInterface
    {
        return new ProcessWorker();
    }
}