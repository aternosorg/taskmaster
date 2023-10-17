<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Task;

class SleepTask extends Task
{
    #[RunOnParent]
    public function __construct(protected int $microseconds)
    {
    }

    #[RunOnChild]
    public function run(): void
    {
        usleep($this->microseconds);
    }
}