<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Generator;

/**
 * Class Socket
 *
 * The default socket that wraps a stream resource, e.g. opened with {@link fopen()} or {@link stream_socket_pair()}.
 *
 * @package Aternos\Taskmaster\Communication\Socket
 */
class Socket implements SocketInterface, SelectableSocketInterface
{

    /**
     * @var resource
     */
    protected mixed $socket;

    /**
     * @param resource|Socket $socket
     */
    public function __construct(mixed $socket)
    {
        if ($socket instanceof Socket) {
            $this->socket = $socket->getStream();
        } else {
            $this->socket = $socket;
        }
        stream_set_blocking($this->socket, false);
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(MessageInterface $message): bool
    {
        return $this->sendRaw(serialize($message));
    }

    /**
     * @inheritDoc
     */
    public function receiveMessages(): Generator
    {
        foreach ($this->receiveRaw() as $data) {
            yield unserialize($data);
        }
    }

    /**
     * @inheritDoc
     */
    public function receiveRaw(): Generator
    {
        do {
            if (!is_resource($this->socket) || feof($this->socket)) {
                throw new SocketReadException("Could not read from socket.");
            }
            $result = fgets($this->socket);
            if (!$result) {
                break;
            }
            $decoded = base64_decode($result);
            yield $decoded;
        } while (true);
    }

    /**
     * Get the stream resource
     *
     * @return resource
     */
    public function getStream(): mixed
    {
        return $this->socket;
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if (!is_resource($this->socket)) {
            return;
        }
        fclose($this->socket);
    }

    /**
     * @inheritDoc
     */
    public function sendRaw(string $data): bool
    {
        if (str_ends_with($data, PHP_EOL)) {
            $data = substr($data, 0, -strlen(PHP_EOL));
        }
        $data = base64_encode($data);
        $data .= PHP_EOL;
        $total = 0;
        $expected = strlen($data);
        do {
            if (!is_resource($this->socket) || feof($this->socket)) {
                throw new SocketWriteException("Could not write to socket.");
            }
            $result = @fwrite($this->socket, $data);
            if ($result === false || $result === 0) {
                throw new SocketWriteException("Could not write to socket.");
            }
            $total += $result;
        } while ($total < $expected);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSelectableReadStream(): mixed
    {
        return $this->socket;
    }

    /**
     * @inheritDoc
     */
    public function waitForNewData(int $microseconds): void
    {
        $read = [$this->socket];
        stream_select($read, $write, $except, 0, $microseconds);
    }
}