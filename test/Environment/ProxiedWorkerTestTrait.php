<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Exception\WorkerFailedException;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Task\SleepStatusTask;

trait ProxiedWorkerTestTrait
{
    protected Taskmaster $taskmaster;

    /**
     * @param TaskInterface $task
     * @param int $amount
     * @return TaskInterface[]
     */
    abstract protected function addTasks(TaskInterface $task, int $amount): array;

    public function testTasksFailOnProxyDeath(): void
    {
        /** @var SleepStatusTask $tasks */
        $tasks = $this->addTasks(new SleepStatusTask(10000), 3);
        do {
            $this->taskmaster->update();
            $runningTasks = 0;
            foreach ($tasks as $task) {
                if ($task->isRunning()) {
                    $runningTasks++;
                }
            }
        } while ($runningTasks < 3);
        foreach ($this->taskmaster->getProxies() as $proxy) {
            $proxy->stop();
        }
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertInstanceOf(WorkerFailedException::class, $task->getError());
        }
    }

    public function testProxyRestartsAfterFail(): void
    {
        /** @var SleepStatusTask $tasks */
        $tasks = $this->addTasks(new SleepStatusTask(10000), 6);
        do {
            $this->taskmaster->update();
            $runningTasks = 0;
            foreach ($tasks as $task) {
                if ($task->isRunning()) {
                    $runningTasks++;
                }
            }
        } while ($runningTasks < 3);
        foreach ($this->taskmaster->getProxies() as $proxy) {
            $proxy->stop();
        }
        $this->taskmaster->wait();
        foreach ($tasks as $i => $task) {
            if ($i < 3) {
                $this->assertInstanceOf(WorkerFailedException::class, $task->getError());
            } else {
                $this->assertNull($task->getError());
            }
        }

    }

    abstract public static function assertNull(mixed $actual, string $message = ''): void;

    abstract public static function assertInstanceOf(string $expected, mixed $actual, string $message = ''): void;
}