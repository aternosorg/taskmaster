<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;

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