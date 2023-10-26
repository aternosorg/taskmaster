<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Exception\WorkerFailedException;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Util\Task\SleepStatusTask;

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
        $tasks = $this->addTasks(new SleepStatusTask(100_000 * $this->getTimeFactor()), 3);
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
        $tasks = $this->addTasks(new SleepStatusTask(100_000 * $this->getTimeFactor()), 6);
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

    abstract protected function getTimeFactor(): int;
}