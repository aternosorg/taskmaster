<?php

namespace Aternos\Taskmaster\Test\Performance;

use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Util\Task\EmptyTaskFactory;
use PhpBench\Attributes\Iterations;

#[Iterations(3)]
class EmptyBench extends TaskmasterBenchCase
{
    /**
     * @param Taskmaster $taskmaster
     * @return void
     */
    protected function setupTasks(Taskmaster $taskmaster): void
    {
        $taskmaster->addTaskFactory(new EmptyTaskFactory(1_000));
    }
}