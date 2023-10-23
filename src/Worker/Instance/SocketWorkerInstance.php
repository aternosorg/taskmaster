<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Socket\Exception\SocketException;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Exception\TaskTimeoutException;
use Exception;
use Throwable;

/**
 * Class SocketWorkerInstance
 *
 * Worker instance that communicates over a socket.
 *
 * @package Aternos\Taskmaster\Worker\Instance
 */
abstract class SocketWorkerInstance extends WorkerInstance implements SocketWorkerInstanceInterface
{
    use SocketCommunicatorTrait {
        update as socketUpdate;
    }

    /**
     * @inheritDoc
     */
    public function getSocket(): ?SocketInterface
    {
        return $this->socket;
    }

    /**
     * @inheritDoc
     */
    public function update(): static
    {
        $this->checkAndHandleTimeout();
        $this->socketUpdate();
        if ($this->status === WorkerInstanceStatus::IDLE || $this->status === WorkerInstanceStatus::WORKING) {
            if ($this->hasDied()) {
                $this->handleFail("Worker exited unexpectedly.");
            }
        }
        return $this;
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function checkAndHandleTimeout(): void
    {
        if (!$this->currentTask || !$this->currentTaskStartTime) {
            return;
        }

        $timeout = $this->currentTask->getTimeout();
        if ($timeout <= 0) {
            return;
        }

        $passedTime = microtime(true) - $this->currentTaskStartTime;
        if ($passedTime < $timeout) {
            return;
        }

        $this->currentTaskStartTime = 0;
        $this->handleFail(new TaskTimeoutException($timeout, $passedTime));
    }

    /**
     * @inheritDoc
     */
    public function handleFail(null|string|Exception $reason = null): static
    {
        if ($this->status === WorkerInstanceStatus::FAILED) {
            return $this;
        }
        $this->status = WorkerInstanceStatus::FAILED;
        try {
            // try to read any last messages
            $this->update();
        } catch (SocketException) {
        }
        return parent::handleFail($reason);
    }
}