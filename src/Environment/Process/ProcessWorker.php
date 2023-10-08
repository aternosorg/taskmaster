<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Worker\SocketWorker;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ProcessWorker extends SocketWorker
{
    protected RuntimeProcess $process;

    public function stop(): void
    {
        $this->process->stop();
    }

    public function start(): Promise
    {
        $this->process = new RuntimeProcess($this->options, ProcessRuntime::class);
        $this->socket = $this->process->getSocket();
        $this->status = WorkerStatus::IDLE;
        return (new Promise())->resolve();
    }
}