<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnBoth;
use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Synchronized;
use Aternos\Taskmaster\Task\Task;
use ReflectionException;
use Throwable;

class SynchronizedFieldTask extends Task
{
    #[Synchronized] protected int $counter = 0;

    #[RunOnParent]
    public function __construct(protected int $amount)
    {
    }

    /**
     * @return void
     */
    #[RunOnBoth]
    public function increaseCounter(): void
    {
        $this->counter++;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    #[RunOnChild]
    public function run(): null
    {
        for ($i = 0; $i < $this->amount; $i++) {
            $this->increaseCounter();
            $this->call($this->increaseCounter(...));
        }
        return null;
    }

    /**
     * @param mixed $result
     * @return void
     */
    public function handleResult(mixed $result): void
    {
        $this->result = $this->counter;
    }
}