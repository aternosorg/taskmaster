<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use parallel\Channel;

class ChannelPair
{
    protected string $id;
    protected Channel $parentToChild;
    protected Channel $childToParent;
    protected ChannelSocket $parentSocket;
    protected ChannelSocket $childSocket;

    public function __construct()
    {
        $this->id = uniqid();
        $this->parentToChild = Channel::make($this->id . "-ptc", Channel::Infinite);
        $this->childToParent = Channel::make($this->id . "-ctp", Channel::Infinite);
        $this->parentSocket = new ChannelSocket($this->parentToChild, $this->childToParent);
        $this->childSocket = new ChannelSocket($this->childToParent, $this->parentToChild);
    }

    /**
     * @return ChannelSocket
     */
    public function getParentSocket(): ChannelSocket
    {
        return $this->parentSocket;
    }

    /**
     * @return ChannelSocket
     */
    public function getChildSocket(): ChannelSocket
    {
        return $this->childSocket;
    }
}