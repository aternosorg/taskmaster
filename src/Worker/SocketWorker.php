<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\TaskmasterOptions;

abstract class SocketWorker extends Worker implements ProxyableWorkerInterface
{
    use SocketCommunicatorTrait;

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
}