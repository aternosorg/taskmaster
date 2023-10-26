<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Environment\Process\ProcessWorker;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ProcessWorkerTest extends ExitableAsyncWorkerTestCase
{
    protected function createWorker(): WorkerInterface
    {
        return new ProcessWorker();
    }
}