<?php

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Runtime\SocketRuntime;
use parallel\Channel;

/**
 * Class ThreadRuntime
 *
 * The thread runtime is started in a new thread by a thread worker instance.
 * It communicates with the thread worker instance using a {@link Channel} pair.
 *
 * @package Aternos\Taskmaster\Environment\Thread
 */
class ThreadRuntime extends SocketRuntime
{
}