<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Worker\Worker;

class ProcessWorker extends Worker
{
    public function createInstance(): ProcessWorkerInstance
    {
        return new ProcessWorkerInstance($this->taskmaster->getOptions());
    }
}