<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;

class LargeParentTask extends Task
{
    #[OnParent] protected string $data;

    public function __construct(int $length = 100_000)
    {
        $this->data = str_repeat("a", $length);
    }

    /**
     * @inheritDoc
     */
    #[OnChild] public function run(): void
    {
    }
}