<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class DestructParentTask extends Task
{
    public function __construct()
    {
        DestructRegistry::register($this);
    }

    /**
     * @inheritDoc
     */
    #[OnChild] public function run(): void
    {
    }

    public function __destruct()
    {
        DestructRegistry::unregister($this);
    }
}