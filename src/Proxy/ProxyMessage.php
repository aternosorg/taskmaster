<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\MessageInterface;

class ProxyMessage implements MessageInterface
{
    protected ?string $id = null;
    protected string $message;

    public function __construct(
        ?string                 $id,
        MessageInterface|string $message
    )
    {
        $this->setId($id);
        $this->setMessage($message);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return $this
     */
    public function setId(?string $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        return unserialize($this->message);
    }

    /**
     * @return string
     */
    public function getMessageString(): string
    {
        return $this->message;
    }

    /**
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