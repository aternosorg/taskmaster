<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Task;
use ReflectionException;
use Throwable;

class CallbackTask extends Task
{
    public static int $counter = 0;

    /**
     * @return void
     */
    public static function resetCounter(): void
    {
        self::$counter = 0;
    }

    #[RunOnParent]
    public function __construct(protected int $amount)
    {
    }

    /**
     * @return int
     */
    #[RunOnParent]
    public function getAndIncreaseCounter(): int
    {
        return self::$counter++;
    }

    /**
     * @return int[]
     * @throws ReflectionException
     * @throws Throwable
     */
    #[RunOnChild]
    public function run(): array
    {
        $result = [];
        for ($i = 0; $i < $this->amount; $i++) {
            $result[] = $this->call($this->getAndIncreaseCounter(...));
        }
        return $result;
    }
}