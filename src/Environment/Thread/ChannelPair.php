<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use parallel\Channel;

/**
 * Class ChannelPair
 *
 * A channel pair is a pair of two {@link Channel}s, one for each direction.
 * The channels are used to communicate between threads.
 *
 * @package Aternos\Taskmaster\Environment\Thread
 */
class ChannelPair
{
    protected string $id;
    protected Channel $parentToChild;
    protected Channel $childToParent;
    protected ChannelSocket $parentSocket;
    protected ChannelSocket $childSocket;

    /**
     * ChannelPair constructor.
     */
    public function __construct()
    {
        $this->id = uniqid();
        $this->parentToChild = Channel::make($this->id . "-ptc", Channel::Infinite);
        $this->childToParent = Channel::make($this->id . "-ctp", Channel::Infinite);
        $this->parentSocket = new ChannelSocket($this->parentToChild, $this->childToParent);
        $this->childSocket = new ChannelSocket($this->childToParent, $this->parentToChild);
    }

    /**
     * Get the {@link ChannelSocket} for the parent process
     *
     * @return ChannelSocket
     */
    public function getParentSocket(): ChannelSocket
    {
        return $this->parentSocket;
    }

    /**
     * Get the {@link ChannelSocket} for the child thread
     *
     * @return ChannelSocket
     */
    public function getChildSocket(): ChannelSocket
    {
        return $this->childSocket;
    }
}