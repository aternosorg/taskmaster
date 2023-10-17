<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\Task;

class ExitTask extends SuppressedErrorOutputTask
{
    #[RunOnChild]
    public function run(): void
    {
        exit;
    }
}