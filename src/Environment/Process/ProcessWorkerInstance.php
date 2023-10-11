<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Worker\SocketWorkerInstance;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ProcessWorkerInstance extends SocketWorkerInstance
{
    protected RuntimeProcess $process;

    public function stop(): static
    {
        $this->process->stop();
        return $this;
    }

    public function start(): Promise
    {
        $this->process = new RuntimeProcess($this->options, ProcessRuntime::class);
        $this->socket = $this->process->getSocket();
        $this->status = WorkerStatus::IDLE;
        return (new Promise())->resolve();
    }
}