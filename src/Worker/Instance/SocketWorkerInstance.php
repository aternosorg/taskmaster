<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Socket\Exception\SocketException;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;

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
        $this->socketUpdate();
        if ($this->status === WorkerInstanceStatus::IDLE || $this->status === WorkerInstanceStatus::WORKING) {
            if ($this->hasDied()) {
                $this->handleFail("Worker exited unexpectedly.");
            }
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handleFail(?string $reason = null): static
    {
        if ($this->status === WorkerInstanceStatus::FAILED) {
            return $this;
        }
        $this->status = WorkerInstanceStatus::FAILED;
        try {
            // try to read any last messages
            $this->update();
        } catch (SocketException $e) {
        }
        return parent::handleFail($reason);
    }
}