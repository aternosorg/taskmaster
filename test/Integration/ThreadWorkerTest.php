<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Environment\Thread\ThreadWorker;
use Aternos\Taskmaster\Worker\WorkerInterface;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension("parallel")]
class ThreadWorkerTest extends AsyncWorkerTestCase
{
    protected function createWorker(): WorkerInterface
    {
        return new ThreadWorker();
    }
}