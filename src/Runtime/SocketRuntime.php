<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Aternos\Taskmaster\Communication\Socket\SelectableSocketInterface;
use Aternos\Taskmaster\Communication\Socket\Socket;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Exception\PhpError;
use Aternos\Taskmaster\Exception\PhpFatalErrorException;
use Aternos\Taskmaster\Taskmaster;
use Exception;

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

        // Force loading of PhpError class, because it might not be possible to load the class in the
        // error handler, e.g. when the open files limit is reached
        class_exists(PhpError::class);
    }

    /**
     * Error handler for {@link set_error_handler()}
     *
     * This handler will send a {@link PhpFatalErrorException} to the master if a fatal error occurs.
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

        $exception = new PhpFatalErrorException($phpError);
        $response = new ExceptionResponse($this->currentTaskRequest->getRequestId(), $exception);
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
    protected function handleFail(null|string|Exception $reason = null): static
    {
        if ($reason instanceof Exception) {
            $reason = $reason->getMessage();
        }
        fwrite(STDERR, "Runtime failed: " . $reason . PHP_EOL);
        exit(1);
    }
}