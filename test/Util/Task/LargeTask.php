<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnBoth;
use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\Task;

class LargeTask extends Task
{
    #[OnBoth] protected string $data;

    /**
     * @param int $length
     */
    public function __construct(int $length = 100_000)
    {
        $this->data = str_repeat("T", $length);
    }

    #[OnChild] public function run()
    {
        return $this->data;
    }
}