<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\MessageInterface;

interface SocketInterface
{
    /**
     * @throws SocketWriteException
     * @param MessageInterface $message
     * @return bool
     */
    public function sendMessage(MessageInterface $message): bool;

    /**
     * @throws SocketWriteException
     * @param string $data
     * @return bool
     */
    public function sendRaw(string $data): bool;

    /**
     * @throws SocketReadException
     * @return iterable
     */
    public function receiveMessages(): iterable;

    /**
     * @throws SocketReadException
     * @return iterable
     */
    public function receiveRaw(): iterable;

    /**
     * @return resource
     */
    public function getStream(): mixed;

    /**
     * @return void
     */
    public function close(): void;
}