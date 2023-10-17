<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;

/**
 * Interface SocketInterface
 *
 * A socket is used to send and receive messages
 *
 * @package Aternos\Taskmaster\Communication\Socket
 */
interface SocketInterface
{
    /**
     * Send a message through the socket
     *
     * @throws SocketWriteException
     * @param MessageInterface $message
     * @return bool
     */
    public function sendMessage(MessageInterface $message): bool;

    /**
     * Send raw data through the socket
     *
     * This can be used by a proxy to forward data without deserializing and serializing it again.
     *
     * @throws SocketWriteException
     * @param string $data
     * @return bool
     */
    public function sendRaw(string $data): bool;

    /**
     * Receive messages from the socket
     *
     * @throws SocketReadException
     * @return iterable<MessageInterface>
     */
    public function receiveMessages(): iterable;

    /**
     * Receive raw data from the socket
     *
     * This can be used by a proxy to forward data without deserializing and serializing it again.
     *
     * @throws SocketReadException
     * @return iterable<string>
     */
    public function receiveRaw(): iterable;

    /**
     * Close the socket
     *
     * @return void
     */
    public function close(): void;
}