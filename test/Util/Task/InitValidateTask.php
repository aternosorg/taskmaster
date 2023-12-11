<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class InitValidateTask extends Task
{
    /**
     * @inheritDoc
     */
    #[OnChild]
    public function run(): bool
    {
        return InitTask::$initialized;
    }
}