<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Task;

abstract class SuppressedErrorOutputTask extends Task
{
    #[RunOnParent]
    public function handleError(\Exception $error): void
    {
        $this->error = $error;
    }
}