<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Runtime\SocketRuntime;

/**
 * Class ProcessRuntime
 *
 * The process runtime is started in the child process by a process worker instance after forking the process.
 * It communicates with the process worker instance using a socket pair.
 *
 * @package Aternos\Taskmaster\Environment\Process
 */
class ProcessRuntime extends SocketRuntime
{

}