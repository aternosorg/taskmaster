<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class SyncTask extends Task
{
    /**
     * @inheritDoc
     */
    #[OnChild]
    public function run(): bool
    {
        return $this->isSync();
    }
}