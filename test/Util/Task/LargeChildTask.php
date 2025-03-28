<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class LargeChildTask extends Task
{
    #[OnChild] protected ?string $data = null;

    public function __construct(#[OnChild] protected int $length = 100_000)
    {
    }

    /**
     * @inheritDoc
     */
    #[OnChild] public function run(): void
    {
        $this->data = str_repeat("a", $this->length);
    }
}