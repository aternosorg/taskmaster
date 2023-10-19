<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Response\PhpError;
use Aternos\Taskmaster\Communication\Response\PhpFatalErrorResponse;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Aternos\Taskmaster\Communication\Socket\SelectableSocketInterface;
use Aternos\Taskmaster\Communication\Socket\Socket;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Taskmaster;

/**
 * Class SocketRuntime
 *
 * A runtime that communicates via a {@link SocketInterface}.
 * If no socket is provided, the runtime will open a socket on file descriptor 3.
 *
 * @package Aternos\Taskmaster\Runtime
 */
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
        set_error_handler($this->handleError(...), E_ALL);
        $this->setReady();
    }

    /**
     * Error handler for {@link set_error_handler()}
     *
     * This handler will send a {@link PhpFatalErrorResponse} to the master if a fatal error occurs.
     * Otherwise, the task will be notified about the error via {@link TaskInterface::handleUncriticalError()}.
     * If {@link TaskInterface::handleUncriticalError()} returns false, the error will be handled by PHP.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     * @noinspection SpellCheckingInspection
     */
    protected function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!$this->currentTaskRequest) {
            return false;
        }
        $phpError = new PhpError($errno, $errstr, $errfile, $errline);

        if (!$phpError->isFatal()) {
            return $this->currentTaskRequest->task->handleUncriticalError($phpError);
        }

        $response = new PhpFatalErrorResponse($this->currentTaskRequest->getRequestId(), $phpError);
        try {
            $this->socket->sendMessage($response);
        } catch (SocketWriteException $e) {
            $this->handleFail($e->getMessage());
        }
        exit(1);
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        while (true) {
            $this->update();
            if ($this->socket instanceof SelectableSocketInterface) {
                $this->socket->waitForNewData(Taskmaster::SOCKET_WAIT_TIME);
            } else {
                usleep(Taskmaster::SOCKET_WAIT_TIME);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function handleFail(?string $reason = null): static
    {
        fwrite(STDERR, "Runtime failed: " . $reason . PHP_EOL);
        exit(1);
    }
}