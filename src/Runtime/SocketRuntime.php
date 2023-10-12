<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Response\PhpErrorResponse;
use Aternos\Taskmaster\Communication\Socket\Socket;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Communication\Socket\SocketWriteException;

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
        try {
            $this->socket->sendMessage($response);
        } catch (SocketWriteException $e) {
            $this->handleFail($e->getMessage());
        }
        exit(1);
    }

    public function start(): void
    {
        while (true) {
            $this->update();
        }
    }

    /**
     * @param string|null $reason
     * @return $this
     */
    protected function handleFail(?string $reason = null): static
    {
        fwrite(STDERR, "Runtime failed: " . $reason . PHP_EOL);
        exit(1);
    }
}