<?php

namespace Aternos\Taskmaster\Test\Performance;

use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Util\Task\EmptyTaskFactory;
use Aternos\Taskmaster\Test\Util\Task\SleepTask;
use PhpBench\Attributes\Iterations;

#[Iterations(3)]
class SleepBench extends TaskmasterBenchCase
{
    /**
     * @param Taskmaster $taskmaster
     * @return void
     */
    protected function setupTasks(Taskmaster $taskmaster): void
    {
        for ($i = 0; $i < 100; $i++) {
            $taskmaster->runTask(new SleepTask(10_000));
        }
    }
}