<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Task;

class AdditionTask extends Task
{
    /**
     * @param int $a
     * @param int $b
     */
    #[RunOnParent]
    public function __construct(protected int $a, protected int $b)
    {
    }

    /**
     * @return int
     */
    #[RunOnChild]
    public function run(): int
    {
        return $this->a + $this->b;
    }

    /**
     * @param mixed $result
     * @return void
     */
    #[RunOnParent]
    public function handleResult(mixed $result): void
    {
        $this->result = $result;
    }
}