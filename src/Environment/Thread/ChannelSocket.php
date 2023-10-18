<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use parallel\Channel;
use parallel\Events;

/**
 * Class ChannelSocket
 *
 * A {@link SocketInterface} implementation using parallel {@link Channel}s. One channel is used for
 * sending and one for receiving. The socket on the other end of the communication must use the
 * channels in the opposite order.
 *
 * @package Aternos\Taskmaster\Environment\Thread
 */
class ChannelSocket implements SocketInterface
{
    protected Events $events;

    /**
     * @param Channel $sender
     * @param Channel $receiver
     */
    public function __construct(protected Channel $sender, protected Channel $receiver)
    {
        $this->events = new Events();
        $this->events->setBlocking(false);
        $this->events->addChannel($this->receiver);
    }

    /**
     * @inheritDoc
     */
    public function sendMessage(MessageInterface $message): bool
    {
        return $this->sendRaw(serialize($message));
    }

    /**
     * @inheritDoc
     */
    public function sendRaw(string $data): bool
    {
        $this->sender->send($data);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function receiveMessages(): iterable
    {
        foreach ($this->receiveRaw() as $data) {
            yield unserialize($data);
        }
    }

    /**
     * @inheritDoc
     */
    public function receiveRaw(): iterable
    {
        while ($event = $this->events->poll()) {
            $this->events->addChannel($this->receiver);
            yield $event->value;
        }
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->sender->close();
        $this->receiver->close();
    }

    /**
     * Get the receiver channel of this socket
     *
     * @return Channel
     */
    public function getReceiver(): Channel
    {
        return $this->receiver;
    }

    /**
     * Get the sender channel of this socket
     *
     * @return Channel
     */
    public function getSender(): Channel
    {
        return $this->sender;
    }

    /**
     * Get both channels of this socket as array, first sender, then receiver
     *
     * This can be used to pass the channels to a thread, as only the channel
     * objects directly may be passed to a thread.
     *
     * @return Channel[]
     */
    public function getChannels(): array
    {
        return [$this->getSender(), $this->getReceiver()];
    }
}