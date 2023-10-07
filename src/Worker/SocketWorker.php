<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\SocketCommunicatorTrait;

abstract class SocketWorker extends Worker
{
    use SocketCommunicatorTrait;
}