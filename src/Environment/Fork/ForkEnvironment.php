<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Environment\Environment;

class ForkEnvironment extends Environment
{


    public function start(): static
    {
        while ($task = $this->taskmaster->getNextTask()) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new \RuntimeException("Could not fork");
            }
            if ($pid === 0) {
                $task->run();
                exit(0);
            }
        }
        return $this;
    }

    public function wait(): static
    {
        while (pcntl_wait($status) <= 0) {
        }
        return $this;
    }
}