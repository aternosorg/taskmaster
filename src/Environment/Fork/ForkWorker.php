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
     * @return bool
     */
    public static function isSupported(): bool
    {
        if (!extension_loaded("pcntl")) {
            return false;
        }

        // GRPC only supports forks if explicitly enabled using the GRPC_ENABLE_FORK_SUPPORT environment variable
        // see https://github.com/grpc/grpc/issues/13412
        // If you don't use GRPC, but have the extension loaded, you can always define your workers manually
        // or override the automatic worker selection using the TASKMASTER_WORKER environment variable
        if (extension_loaded("grpc") && getenv("GRPC_ENABLE_FORK_SUPPORT") !== "1") {
            return false;
        }

        return true;
    }

    /**
     * @return WorkerInstanceInterface
     */
    public function createInstance(): WorkerInstanceInterface
    {
        return new ForkWorkerInstance($this->options);
    }
}