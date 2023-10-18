<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Runtime\SocketRuntime;

/**
 * Class ForkRuntime
 *
 * The fork runtime is started in the child process by a fork worker instance after forking the process.
 * It communicates with the fork worker instance using a socket pair.
 *
 * @package Aternos\Taskmaster\Environment\Fork
 */
class ForkRuntime extends SocketRuntime
{
}