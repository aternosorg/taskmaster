<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Test\Task\SuppressedErrorOutputTask;

class InterruptableSleepTask extends SuppressedErrorOutputTask
{
    #[OnParent]
    public function __construct(public int $microseconds, protected int $interval = 100)
    {
    }

    #[OnChild]
    public function run(): void
    {
        for ($i = 0; $i < $this->microseconds; $i+=$this->interval) {
            usleep($this->interval);
        }
    }
}