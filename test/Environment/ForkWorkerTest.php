<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Environment\Fork\ForkWorker;
use Aternos\Taskmaster\Worker\WorkerInterface;

class ForkWorkerTest extends AsyncWorkerTestCase
{
    protected function createWorker(): WorkerInterface
    {
        return new ForkWorker();
    }
}