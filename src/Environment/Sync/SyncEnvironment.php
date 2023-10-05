<?php

namespace Aternos\Taskmaster\Environment\Sync;

use Aternos\Taskmaster\Environment\Environment;

class SyncEnvironment extends Environment
{
    public function start(): static
    {
        while ($task = $this->taskmaster->getNextTask()) {
            $task->run();
        }
        return $this;
    }

    public function wait(): static
    {
        return $this;
    }
}