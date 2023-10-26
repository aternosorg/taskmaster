<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
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

    #[OnParent]
    public function __construct(protected int $amount)
    {
    }

    /**
     * @return int
     */
    #[OnParent]
    public function getAndIncreaseCounter(): int
    {
        return self::$counter++;
    }

    /**
     * @return int[]
     * @throws ReflectionException
     * @throws Throwable
     */
    #[OnChild]
    public function run(): array
    {
        $result = [];
        for ($i = 0; $i < $this->amount; $i++) {
            $result[] = $this->call($this->getAndIncreaseCounter(...));
        }
        return $result;
    }
}