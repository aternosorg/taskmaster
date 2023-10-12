<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\MessageInterface;
use Generator;

class Socket implements SocketInterface
{
    /**
     * @param resource $socket
     */
    public function __construct(protected mixed $socket)
    {
        if ($this->socket instanceof SocketInterface) {
            $this->socket = $this->socket->getStream();
        }
        stream_set_blocking($this->socket, false);
    }

    /**
     * @param MessageInterface $message
     * @return bool
     * @throws SocketWriteException
     */
    public function sendMessage(MessageInterface $message): bool
    {
        return $this->sendRaw(serialize($message));
    }

    /**
     * @return Generator
     * @throws SocketReadException
     */
    public function receiveMessages(): Generator
    {
        foreach ($this->receiveRaw() as $data) {
            yield unserialize($data);
        }
    }

    /**
     * @return Generator<string>
     * @throws SocketReadException
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
     * @return resource
     */
    public function getStream(): mixed
    {
        return $this->socket;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if (!is_resource($this->socket)) {
            return;
        }
        fclose($this->socket);
    }

    /**
     * @param string $data
     * @return bool
     * @throws SocketWriteException
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
}