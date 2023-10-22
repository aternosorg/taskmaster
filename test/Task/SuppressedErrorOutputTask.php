<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;

abstract class SuppressedErrorOutputTask extends Task
{
    #[OnParent]
    public function handleError(\Exception $error): void
    {
        $this->error = $error;
    }
}