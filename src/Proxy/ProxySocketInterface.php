<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;

interface ProxySocketInterface extends SocketInterface
{
    /**
     * @param string|null $id
     * @param MessageInterface|string $message
     * @return bool
     */
    public function sendProxyMessage(?string $id, MessageInterface|string $message): bool;

    /**
     * @param string|null $id
     * @return iterable
     */
    public function receiveProxyMessages(?string $id): iterable;

    /**
     * @param string|null $id
     * @return iterable
     */
    public function receiveRawProxyMessages(?string $id): iterable;

    /**
     * @return iterable
     */
    public function getUnhandledMessages(): iterable;

    /**
     * @return $this
     */
    public function clearUnhandledMessages(): static;
}