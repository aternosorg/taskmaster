<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Communication\MessageInterface;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use parallel\Channel;
use parallel\Events;

class ChannelSocket implements SocketInterface
{
    protected Events $events;

    public function __construct(protected Channel $sender, protected Channel $receiver)
    {
        $this->events = new Events();
        $this->events->setBlocking(false);
        $this->events->addChannel($this->receiver);
    }

    public function sendMessage(MessageInterface $message): bool
    {
        return $this->sendRaw(serialize($message));
    }

    public function sendRaw(string $data): bool
    {
        $this->sender->send($data);
        return true;
    }

    public function receiveMessages(): iterable
    {
        foreach ($this->receiveRaw() as $data) {
            yield unserialize($data);
        }
    }

    public function receiveRaw(): iterable
    {
        while ($event = $this->events->poll()) {
            $this->events->addChannel($this->receiver);
            yield $event->value;
        }
    }

    public function getStream(): Channel
    {
        return $this->sender;
    }

    public function close(): void
    {
        $this->sender->close();
        $this->receiver->close();
    }

    /**
     * @return Channel
     */
    public function getReceiver(): Channel
    {
        return $this->receiver;
    }

    /**
     * @return Channel
     */
    public function getSender(): Channel
    {
        return $this->sender;
    }

    /**
     * @return array
     */
    public function getChannels(): array
    {
        return [$this->sender, $this->receiver];
    }
}