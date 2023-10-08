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
     */
    public function sendMessage(MessageInterface $message): bool
    {
        return $this->sendRaw(serialize($message));
    }

    /**
     * @return Generator
     */
    public function receiveMessages(): Generator
    {
        foreach ($this->receiveRaw() as $data) {
            yield unserialize($data);
        }
    }

    /**
     * @return Generator<string>
     */
    public function receiveRaw(): Generator
    {
        while ($result = fgets($this->socket)) {
            $result = base64_decode($result);
            yield $result;
        }
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
        fclose($this->socket);
    }

    /**
     * @param string $data
     * @return bool
     */
    public function sendRaw(string $data): bool
    {
        if (str_ends_with($data, PHP_EOL)) {
            $data = substr($data, 0, -strlen(PHP_EOL));
        }
        $data = base64_encode($data);
        $data .= PHP_EOL;
        return fwrite($this->socket, $data) !== false;
    }
}