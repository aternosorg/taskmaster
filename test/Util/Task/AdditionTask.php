<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;

class AdditionTask extends Task
{
    /**
     * @param int $a
     * @param int $b
     */
    #[OnParent]
    public function __construct(protected int $a, protected int $b)
    {
    }

    /**
     * @return int
     */
    #[OnChild]
    public function run(): int
    {
        return $this->a + $this->b;
    }

    /**
     * @param mixed $result
     * @return void
     */
    #[OnParent]
    public function handleResult(mixed $result): void
    {
        $this->result = $result;
    }
}