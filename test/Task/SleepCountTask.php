<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\Synchronized;
use Aternos\Taskmaster\Task\Task;
use ReflectionException;
use Throwable;

class SleepCountTask extends Task
{
    static protected int $current = 0;

    #[Synchronized] protected int $counter = 0;

    public function __construct(public string $name, protected int $countTarget, protected int $sleepTime = 1)
    {
    }

    public function getCurrent(): int
    {
        $this->increaseAndOutputCounter();
        return static::$current++;
    }

    protected function increaseAndOutputCounter(): void
    {
        $this->counter++;
        echo "    " . $this->name . " counter: " . $this->counter . PHP_EOL;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function run(): int
    {
        $current = 0;
        for ($i = 0; $i < $this->countTarget; $i++) {
            $this->increaseAndOutputCounter();
            sleep($this->sleepTime);
            $current = $this->call($this->getCurrent(...));
            echo $current . " | " . $this->name . ": " . $i . PHP_EOL;
        }
        //trigger_error("Test error", E_USER_ERROR);
        return $current;
    }

    public function handleResult(mixed $result): void
    {
        $this->increaseAndOutputCounter();
        echo $this->name . " finished after " . $result . PHP_EOL;
    }
}