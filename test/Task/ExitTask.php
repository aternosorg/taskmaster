<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class ExitTask extends SuppressedErrorOutputTask
{
    #[OnChild]
    public function run(): void
    {
        exit;
    }
}