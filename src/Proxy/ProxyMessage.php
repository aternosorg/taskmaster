<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;

/**
 * Class ProxyMessage
 *
 * A proxy message wraps a serialized message with an id to identify the {@link ProxiedSocket} the message came from
 * and is being sent to.
 *
 * @package Aternos\Taskmaster\Proxy
 */
class ProxyMessage implements MessageInterface
{
    protected ?string $id = null;
    protected string $message;

    /**
     * @param string|null $id
     * @param MessageInterface|string $message
     */
    public function __construct(
        ?string                 $id,
        MessageInterface|string $message
    )
    {
        $this->setId($id);
        $this->setMessage($message);
    }

    /**
     * Get the id of the {@link ProxiedSocket} the message came from and is being sent to.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set the id of the {@link ProxiedSocket} the message came from and is being sent to.
     *
     * @param string|null $id
     * @return $this
     */
    public function setId(?string $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the wrapped unserialized message.
     *
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        return unserialize($this->message);
    }

    /**
     * Get the wrapped serialized message.
     *
     * @return string
     */
    public function getMessageString(): string
    {
        return $this->message;
    }

    /**
     * Set the wrapped message
     *
     * If necessary, the message will be serialized.
     *
     * @param MessageInterface|string $message
     * @return $this
     */
    public function setMessage(MessageInterface|string $message): static
    {
        if (!is_string($message)) {
            $message = serialize($message);
        }
        $this->message = $message;
        return $this;
    }
}