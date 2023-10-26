<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class EmptyTask extends Task
{
    #[OnChild]
    public function run(): void
    {
    }
}