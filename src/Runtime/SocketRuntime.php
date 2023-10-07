<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\SocketCommunicatorTrait;

class SocketRuntime extends Runtime
{
    use SocketCommunicatorTrait;

    /**
     * @param mixed $readSocket
     * @param mixed $writeSocket
     */
    public function __construct(protected mixed $readSocket, protected mixed $writeSocket = null)
    {
        if ($this->writeSocket === null) {
            $this->writeSocket = $this->readSocket;
        }
        parent::__construct();
    }

    public function start(): void
    {
        while (true) {
            $this->update();
        }
    }
}