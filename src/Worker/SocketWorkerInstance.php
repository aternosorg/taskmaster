<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Request\WorkerDiedRequest;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketException;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\TaskmasterOptions;

abstract class SocketWorkerInstance extends WorkerInstance
{
    use SocketCommunicatorTrait {
        update as socketUpdate;
    }

    protected string $id;

    public function __construct(TaskmasterOptions $options)
    {
        parent::__construct($options);
        $this->id = uniqid();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return SocketInterface
     */
    public function getSocket(): SocketInterface
    {
        return $this->socket;
    }

    /**
     * @param SocketInterface $socket
     * @return $this
     */
    public function setSocket(SocketInterface $socket): static
    {
        $this->socket = $socket;
        return $this;
    }

    public function setStatus(WorkerStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function update(): static
    {
        $this->socketUpdate();
        if ($this->status === WorkerStatus::IDLE || $this->status === WorkerStatus::WORKING) {
            if ($this->hasDied()) {
                $this->handleFail("Worker exited unexpectedly.");
            }
        }
        return $this;
    }

    protected function handleFail(?string $reason = null): static
    {
        if ($this->status === WorkerStatus::FAILED) {
            return $this;
        }
        $this->status = WorkerStatus::FAILED;
        try {
            // try to read any last messages
            $this->update();
        } catch (SocketException $e) {
        }
        return parent::handleFail($reason);
    }

    /**
     * @return bool
     */
    abstract public function hasDied(): bool;
}