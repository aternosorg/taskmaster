<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\MessageInterface;

interface SocketInterface
{
    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function sendMessage(MessageInterface $message): bool;

    /**
     * @param string $data
     * @return bool
     */
    public function sendRaw(string $data): bool;

    /**
     * @return iterable
     */
    public function receiveMessages(): iterable;

    /**
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