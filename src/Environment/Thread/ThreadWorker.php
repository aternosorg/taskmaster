<?php

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\SocketWorker;

/**
 * Class ThreadWorker
 *
 * NOTE: This worker is considered experimental and not recommended for production use.
 *       The parallel extension requires a thread safe PHP build and can cause unexpected behavior.
 *       Using {@link exit()} or causing a fatal error in a task can lead to a segfault.
 *       Internal function calls cannot be killed, e.g. due to a timeout.
 *
 * The thread worker starts the thread runtime in a separate thread using the parallel extension.
 * The parallel extension is required for this worker.
 * A {@link ProcessProxy} can be used to start the thread worker in a separate CLI process.
 *
 * @see https://www.php.net/manual/en/book.parallel.php
 * @package Aternos\Taskmaster\Environment\Thread
 */
class ThreadWorker extends SocketWorker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new ThreadWorkerInstance($this->options);
    }
}