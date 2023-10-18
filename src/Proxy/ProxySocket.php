<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Socket;
use Generator;

/**
 * Class ProxySocket
 *
 * A proxy socket connects the proxy to the proxy runtime. It sends and receives {@link ProxyMessage}s.
 * A proxy message stores messages until they are received by a {@link ProxiedSocket}.
 *
 * @package Aternos\Taskmaster\Proxy
 */
class ProxySocket extends Socket implements ProxySocketInterface
{
    /**
     * @var ProxyMessage[]
     */
    protected array $messages = [];

    /**
     * @inheritDoc
     */
    public function sendProxyMessage(?string $id, MessageInterface|string $message): bool
    {
        return $this->sendMessage(new ProxyMessage($id, $message));
    }

    /**
     * @inheritDoc
     */
    public function getUnhandledMessages(): array
    {
        return $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function clearUnhandledMessages(): static
    {
        $this->messages = [];
        return $this;
    }

    /**
     * Read all available messages from the socket and stores them in {@link ProxySocket::$messages}
     *
     * @return void
     * @throws SocketReadException
     */
    protected function readMessages(): void
    {
        foreach ($this->receiveMessages() as $message) {
            $this->messages[] = $message;
        }
    }

    /**
     * @inheritDoc
     */
    public function receiveProxyMessages(?string $id): Generator
    {
        foreach ($this->readProxyMessages($id) as $message) {
            yield $message->getMessage();
        }
    }

    /**
     * @inheritDoc
     */
    public function receiveRawProxyMessages(?string $id): Generator
    {
        foreach ($this->readProxyMessages($id) as $message) {
            yield $message->getMessageString();
        }
    }

    /**
     * Read messages from the socket and return the messages matching $id
     *
     * @param string|null $id
     * @return Generator<ProxyMessage>
     * @throws SocketReadException
     */
    protected function readProxyMessages(?string $id): Generator
    {
        $this->readMessages();
        foreach ($this->messages as $key => $message) {
            if ($message->getId() === $id) {
                unset($this->messages[$key]);
                yield $message;
            }
        }
    }
}