<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerInterface;

/**
 * Class SyncWorker
 *
 * A {@link WorkerInterface} implementation that runs tasks synchronously in the same process.
 * This worker can be used as a fallback or if you don't have enough tasks to justify separate processes.
 * It's not necessary to add more than one sync worker to your {@link Taskmaster}.
 *
 * NOTE: Of course it's not possible to catch fatal errors or call {@link exit()} in a sync worker.
 * Currently, the sync worker doesn't handle any errors, so {@link TaskInterface::handleUncriticalError()} will not
 * be called, but you can register your own error handler using {@link set_error_handler()}.
 *
 * @package Aternos\Taskmaster\Environment\Sync
 */
class SyncWorker extends Worker
{
    public function createInstance(): WorkerInstanceInterface
    {
        return new SyncWorkerInstance($this->options);
    }
}