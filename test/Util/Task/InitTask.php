<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class InitTask extends Task
{
    public static bool $initialized = false;

    /**
     * @inheritDoc
     */
    #[OnChild]
    public function run(): void
    {
        static::$initialized = true;
    }
}