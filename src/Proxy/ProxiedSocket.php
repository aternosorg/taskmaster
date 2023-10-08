<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;

class ProxiedSocket implements SocketInterface
{
    public function __construct(protected ProxySocketInterface $socket, protected ?string $id)
    {
    }

    public function sendMessage(MessageInterface $message): bool
    {
        return $this->socket->sendProxyMessage($this->id, $message);
    }

    public function receiveMessages(): iterable
    {
        return $this->socket->receiveProxyMessages($this->id);
    }

    /**
     * @return mixed
     */
    public function getStream(): mixed
    {
        return $this->socket->getStream();
    }

    public function close(): void
    {
    }

    public function sendRaw(string $data): bool
    {
        return $this->socket->sendProxyMessage($this->id, $data);
    }

    public function receiveRaw(): iterable
    {

    }
}