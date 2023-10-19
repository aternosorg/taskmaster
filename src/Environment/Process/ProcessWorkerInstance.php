<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Worker\Instance\ProxyableSocketWorkerInstance;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceStatus;

/**
 * Class ProcessWorkerInstance
 *
 * The process worker instance creates a new process using {@link proc_open()}.
 * When a process worker instance dies or is stopped, the process worker creates
 * a new process worker instance.
 *
 * @package Aternos\Taskmaster\Environment\Process
 */
class ProcessWorkerInstance extends ProxyableSocketWorkerInstance
{
    protected ?RuntimeProcess $process = null;

    /**
     * @inheritDoc
     */
    public function stop(): static
    {
        if ($this->status !== WorkerInstanceStatus::FAILED) {
            $this->status = WorkerInstanceStatus::FINISHED;
        }
        $this->process?->stop();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function start(): static
    {
        $this->process = new RuntimeProcess($this->options, ProcessRuntime::class);
        $this->socket = $this->process->getSocket();
        $this->status = WorkerInstanceStatus::STARTING;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasDied(): bool
    {
        if ($this->process === null) {
            return false;
        }
        return !$this->process->isRunning();
    }
}