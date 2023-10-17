<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Aternos\Taskmaster\Communication\Socket\Socket;
use Generator;

class ProxySocket extends Socket implements ProxySocketInterface
{
    /**
     * @var ProxyMessage[]
     */
    protected array $messages = [];

    /**
     * @param string|null $id
     * @param MessageInterface|string $message
     * @return bool
     * @throws SocketWriteException
     */
    public function sendProxyMessage(?string $id, MessageInterface|string $message): bool
    {
        return $this->sendMessage(new ProxyMessage($id, $message));
    }

    /**
     * @return ProxyMessage[]
     */
    public function getUnhandledMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return ProxySocket
     */
    public function clearUnhandledMessages(): static
    {
        $this->messages = [];
        return $this;
    }

    /**
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
     * @param string|null $id
     * @return Generator<MessageInterface>
     * @throws SocketReadException
     */
    public function receiveProxyMessages(?string $id): Generator
    {
        foreach ($this->readProxyMessages($id) as $message) {
            yield $message->getMessage();
        }
    }

    /**
     * @param string|null $id
     * @return Generator<string>
     * @throws SocketReadException
     */
    public function receiveRawProxyMessages(?string $id): Generator
    {
        foreach ($this->readProxyMessages($id) as $message) {
            yield $message->getMessageString();
        }
    }

    /**
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