<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;

class SleepStatusTask extends SuppressedErrorOutputTask
{
    protected bool $running = false;

    #[OnParent]
    public function __construct(protected int $microseconds)
    {
    }

    #[OnParent]
    public function setRunning(): void
    {
        $this->running = true;
    }

    #[OnChild]
    public function run(): void
    {
        $this->call($this->setRunning(...));
        usleep($this->microseconds);
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->running;
    }
}