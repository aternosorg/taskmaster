<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Environment\Fork\ForkWorker;
use Aternos\Taskmaster\Worker\WorkerInterface;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension("pcntl")]
class ForkWorkerTest extends ExitableAsyncWorkerTestCase
{
    protected function createWorker(): WorkerInterface
    {
        return new ForkWorker();
    }
}