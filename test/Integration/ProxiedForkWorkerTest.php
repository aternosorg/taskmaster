<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Worker\WorkerInterface;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension("pcntl")]
class ProxiedForkWorkerTest extends ForkWorkerTest
{
    use ProxiedWorkerTestTrait;

    protected function createWorker(): WorkerInterface
    {
        return parent::createWorker()->setProxy(new ProcessProxy());
    }
}