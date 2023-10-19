<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use BadMethodCallException;

/**
 * Class ProxiedSocket
 *
 * A proxied socket represents a socket connection that is sent through a {@link ProxySocketInterface}.
 * The proxied socket is identified by a unique id.
 *
 * @package Aternos\Taskmaster\Proxy
 */
class ProxiedSocket implements SocketInterface
{
    /**
     * @param ProxySocketInterface $socket
     * @param string|null $id
     */
    public function __construct(protected ProxySocketInterface $socket, protected ?string $id)
    {
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(MessageInterface $message): bool
    {
        return $this->socket->sendProxyMessage($this->id, $message);
    }

    /**
     * @inheritDoc
     */
    public function receiveMessages(): iterable
    {
        return $this->socket->receiveProxyMessages($this->id);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function sendRaw(string $data): bool
    {
        return $this->socket->sendProxyMessage($this->id, $data);
    }

    /**
     * @inheritDoc
     */
    public function receiveRaw(): iterable
    {
        throw new BadMethodCallException("Receiving raw data is not supported for proxied sockets.");
    }
}