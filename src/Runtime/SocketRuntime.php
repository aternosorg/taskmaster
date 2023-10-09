<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Response\PhpErrorResponse;
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
        set_error_handler($this->handleError(...), E_ERROR | E_USER_ERROR);
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return void
     */
    protected function handleError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        if (!$this->currentTaskRequest) {
            return;
        }
        $response = new PhpErrorResponse($this->currentTaskRequest->getRequestId(), $errstr, $errno, $errfile, $errline);
        $this->socket->sendMessage($response);
        exit(1);
    }

    public function start(): void
    {
        while (true) {
            $this->update();
        }
    }
}