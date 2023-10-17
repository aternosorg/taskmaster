<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\Task;

class EmptyTask extends Task
{
    #[RunOnChild]
    public function run(): null
    {
        return null;
    }
}