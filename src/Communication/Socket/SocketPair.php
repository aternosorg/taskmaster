<?php

namespace Aternos\Taskmaster\Communication\Socket;

class SocketPair
{
    protected Socket $parentSocket;
    protected Socket $childSocket;

    public function __construct()
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $this->parentSocket = new Socket($pair[0]);
        $this->childSocket = new Socket($pair[1]);
    }

    /**
     * @return void
     */
    public function closeChildSocket(): void
    {
        $this->childSocket->close();
    }

    /**
     * @return void
     */
    public function closeParentSocket(): void
    {
        $this->parentSocket->close();
    }

    /**
     * @return Socket
     */
    public function getParentSocket(): Socket
    {
        return $this->parentSocket;
    }

    /**
     * @return Socket
     */
    public function getChildSocket(): Socket
    {
        return $this->childSocket;
    }
}