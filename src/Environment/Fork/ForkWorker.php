<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\SocketWorker;

/**
 * Class ForkWorker
 *
 * The fork worker forks the php process using the pcntl extension.
 * The pcntl extension is required for this worker, but not available in all environments.
 * A {@link ProcessProxy} can be used to start the fork worker in a separate CLI process.
 *
 * @see https://www.php.net/manual/en/function.pcntl-fork.php
 * @package Aternos\Taskmaster\Environment\Fork
 */
class ForkWorker extends SocketWorker
{
    /**
     * @return WorkerInstanceInterface
     */
    public function createInstance(): WorkerInstanceInterface
    {
        return new ForkWorkerInstance($this->options);
    }
}