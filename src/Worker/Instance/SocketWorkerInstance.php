<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketException;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\WorkerInstanceStatus;

abstract class SocketWorkerInstance extends WorkerInstance implements SocketWorkerInstanceInterface
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
     * @return SocketInterface|null
     */
    public function getSocket(): ?SocketInterface
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

    public function setStatus(WorkerInstanceStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function update(): static
    {
        $this->socketUpdate();
        if ($this->status === WorkerInstanceStatus::IDLE || $this->status === WorkerInstanceStatus::WORKING) {
            if ($this->hasDied()) {
                var_dump("worker died");
                $this->handleFail("Worker exited unexpectedly.");
            }
        }
        return $this;
    }

    protected function handleFail(?string $reason = null): static
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

    /**
     * @return bool
     */
    abstract public function hasDied(): bool;
}