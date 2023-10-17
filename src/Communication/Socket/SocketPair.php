<?php

namespace Aternos\Taskmaster\Communication\Socket;

/**
 * Class SocketPair
 *
 * Creates a pair of connected sockets using {@link stream_socket_pair()} and wraps them in {@link Socket} objects.
 * Also clearly defines which socket is the parent and which is the child.
 *
 * @package Aternos\Taskmaster\Communication\Socket
 */
class SocketPair
{
    protected Socket $parentSocket;
    protected Socket $childSocket;

    /**
     * SocketPair constructor.
     *
     * Creates the socket pair and wraps the sockets in {@link Socket} objects.
     */
    public function __construct()
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $this->parentSocket = new Socket($pair[0]);
        $this->childSocket = new Socket($pair[1]);
    }

    /**
     * Close the child socket
     *
     * @return void
     */
    public function closeChildSocket(): void
    {
        $this->childSocket->close();
    }

    /**
     * Close the parent socket
     *
     * @return void
     */
    public function closeParentSocket(): void
    {
        $this->parentSocket->close();
    }

    /**
     * Get the parent socket
     *
     * @return Socket
     */
    public function getParentSocket(): Socket
    {
        return $this->parentSocket;
    }

    /**
     * Get the child socket
     *
     * @return Socket
     */
    public function getChildSocket(): Socket
    {
        return $this->childSocket;
    }
}