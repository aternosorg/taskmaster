<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Worker\SocketWorker;

class ProcessWorker extends SocketWorker
{
    public function createInstance(): ProcessWorkerInstance
    {
        return new ProcessWorkerInstance($this->taskmaster->getOptions());
    }
}