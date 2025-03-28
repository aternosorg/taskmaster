<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class DestructChildTask extends Task
{
    /**
     * @inheritDoc
     */
    #[OnChild] public function run(): int
    {
        $countBefore = DestructRegistry::count();
        DestructRegistry::register($this);
        return $countBefore;
    }

    public function __destruct()
    {
        DestructRegistry::unregister($this);
    }
}