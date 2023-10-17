<?php

namespace Aternos\Taskmaster\Communication\Socket;

/**
 * Interface SelectableSocketInterface
 *
 * A socket that can be used with {@see stream_select()}
 *
 * @package Aternos\Taskmaster\Communication\Socket
 */
interface SelectableSocketInterface
{
    /**
     * Get the stream resource that can be used with {@see stream_select()}
     *
     * @return resource
     */
    public function getSelectableReadStream(): mixed;

    /**
     * Wait for new data to be available on the socket for up to $microseconds
     *
     * @param int $microseconds
     * @return void
     */
    public function waitForNewData(int $microseconds): void;
}