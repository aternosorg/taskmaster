<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\Task;
use ReflectionException;
use Throwable;

class SleepCountTask extends Task
{
    static protected int $current = 0;

    public function __construct(protected string $name, protected int $countTarget, protected int $sleepTime = 1)
    {
    }

    public function getCurrent(): int
    {
        return static::$current++;
    }

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function run(): int
    {
        $current = 0;
        for ($i = 0; $i < $this->countTarget; $i++) {
            sleep($this->sleepTime);
            $current = $this->call($this->getCurrent(...));
            echo $current . " | " . $this->name . ": " . $i . PHP_EOL;
        }
        return $current;
    }

    public function handleResult(mixed $result): void
    {
        echo $this->name . " finished after " . $result . PHP_EOL;
    }
}