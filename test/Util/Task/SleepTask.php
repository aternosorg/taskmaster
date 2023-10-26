<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;

class SleepTask extends Task
{
    #[OnParent]
    public function __construct(protected int $microseconds)
    {
    }

    #[OnChild]
    public function run(): void
    {
        usleep($this->microseconds);
    }
}