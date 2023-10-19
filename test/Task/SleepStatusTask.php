<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;

class SleepStatusTask extends SuppressedErrorOutputTask
{
    protected bool $running = false;

    #[RunOnParent]
    public function __construct(protected int $microseconds)
    {
    }

    #[RunOnParent]
    public function setRunning(): void
    {
        $this->running = true;
    }

    #[RunOnChild]
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