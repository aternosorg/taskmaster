<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Worker\Instance\ProxyableSocketWorkerInstance;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ProcessWorkerInstance extends ProxyableSocketWorkerInstance
{
    protected ?RuntimeProcess $process = null;

    public function stop(): static
    {
        $this->process->stop();
        return $this;
    }

    public function start(): Promise
    {
        $this->process = new RuntimeProcess($this->options, ProcessRuntime::class);
        $this->socket = $this->process->getSocket();
        $this->status = WorkerStatus::STARTING;
        return (new Promise())->resolve();
    }

    /**
     * @return bool
     */
    public function hasDied(): bool
    {
        if ($this->process === null) {
            return false;
        }
        return !$this->process->isRunning();
    }
}