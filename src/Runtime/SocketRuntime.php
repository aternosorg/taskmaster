<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Socket\Socket;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;

class SocketRuntime extends Runtime implements AsyncRuntimeInterface
{
    use SocketCommunicatorTrait;

    /**
     * @param SocketInterface|null $socket
     */
    public function __construct(?SocketInterface $socket = null)
    {
        parent::__construct();
        if ($socket === null) {
            $this->socket = new Socket(fopen("php://fd/3", ""));
        } else {
            $this->socket = $socket;
        }
    }

    public function start(): void
    {
        while (true) {
            $this->update();
        }
    }
}