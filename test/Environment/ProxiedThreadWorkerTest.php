<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Worker\WorkerInterface;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

#[RequiresPhpExtension("parallel")]
class ProxiedThreadWorkerTest extends ThreadWorkerTest
{
    protected function createWorker(): WorkerInterface
    {
        return parent::createWorker()->setProxy(new ProcessProxy());
    }
}