<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnBoth;
use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;
use ReflectionException;
use Throwable;

class SynchronizedFieldTask extends Task
{
    #[OnBoth] protected int $counter = 0;

    #[OnParent]
    public function __construct(protected int $amount)
    {
    }

    /**
     * @return void
     */
    #[OnBoth]
    public function increaseCounter(): void
    {
        $this->counter++;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    #[OnChild]
    public function run(): void
    {
        for ($i = 0; $i < $this->amount; $i++) {
            $this->increaseCounter();
            $this->call($this->increaseCounter(...));
        }
    }

    /**
     * @param mixed $result
     * @return void
     */
    #[OnParent]
    public function handleResult(mixed $result): void
    {
        $this->result = $this->counter;
    }
}