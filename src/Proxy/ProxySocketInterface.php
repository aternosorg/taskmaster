<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;

/**
 * Interface ProxySocketInterface
 *
 * A proxy socket connects the proxy to the proxy runtime. It sends and receives {@link ProxyMessage}s.
 * A proxy message stores messages until they are received by a {@link ProxiedSocket}.
 *
 * @package Aternos\Taskmaster\Proxy
 */
interface ProxySocketInterface extends SocketInterface
{
    /**
     * Send a message through the proxy socket
     *
     * The {@link ProxiedSocket} is identified by the id.
     * The message is serialized.
     *
     * @param string|null $id
     * @param MessageInterface|string $message
     * @return bool
     * @throws SocketWriteException
     */
    public function sendProxyMessage(?string $id, MessageInterface|string $message): bool;

    /**
     * Receive all messages for $id, that weren't handled yet
     *
     * @param string|null $id
     * @return iterable<MessageInterface>
     * @throws SocketReadException
     */
    public function receiveProxyMessages(?string $id): iterable;

    /**
     * Receive all raw serialized messages for $id, that weren't handled yet
     *
     * @param string|null $id
     * @return iterable<string>
     * @throws SocketReadException
     */
    public function receiveRawProxyMessages(?string $id): iterable;

    /**
     * Get all messages that are not yet received by a {@link ProxiedSocket}
     *
     * @return ProxyMessage[]
     */
    public function getUnhandledMessages(): iterable;

    /**
     * Clear all messages that are not yet received by a {@link ProxiedSocket}
     *
     * @return $this
     */
    public function clearUnhandledMessages(): static;
}