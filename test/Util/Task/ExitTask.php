<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;

class ExitTask extends SuppressedErrorOutputTask
{
    #[OnChild]
    public function run(): void
    {
        exit;
    }
}