<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Exception\PhpError;
use Aternos\Taskmaster\Exception\TaskTimeoutException;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Util\Task\EmptyTask;
use Aternos\Taskmaster\Test\Util\Task\InitTask;
use Aternos\Taskmaster\Test\Util\Task\InitValidateTask;
use Aternos\Taskmaster\Test\Util\Task\InterruptableSleepTask;
use Aternos\Taskmaster\Test\Util\Task\SleepTask;
use Aternos\Taskmaster\Test\Util\Task\SyncTask;
use Aternos\Taskmaster\Test\Util\Task\WarningTask;
use Aternos\Taskmaster\Worker\WorkerInterface;

abstract class AsyncWorkerTestCase extends WorkerTestCase
{
    abstract protected function createWorker(): WorkerInterface;

    protected function createTaskmaster(): void
    {
        $this->taskmaster = new Taskmaster();
        $this->taskmaster->addWorkers($this->createWorker(), 3);
    }

    public function testMultipleTasksRunAtTheSameTime(): void
    {
        $time = 20_000 * $this->getTimeFactor();
        $start = microtime(true);
        $this->addTasks(new SleepTask($time), 3);
        $this->taskmaster->wait();
        $end = microtime(true);
        $time = ($end - $start) * 1_000_000;
        $this->assertLessThan($time * 3 - 1, $time);
    }

    public function testHandleWarning(): void
    {
        $tasks = $this->addTasks(new WarningTask(), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertInstanceOf(WarningTask::class, $task);
            $this->assertNull($task->getError());
            $this->assertInstanceOf(PhpError::class, $task->getPhpError());
            $this->assertEquals("Test", $task->getPhpError()->getMessage());
            $this->assertEquals(E_USER_WARNING, $task->getPhpError()->getLevel());
            $this->assertEquals("Warning", $task->getPhpError()->getLevelString());
        }
    }

    public function testDefaultTimeout(): void
    {
        $this->taskmaster->setDefaultTaskTimeout(0.005 * $this->getTimeFactor());
        $tasks = $this->addTasks(new InterruptableSleepTask(10_000 * $this->getTimeFactor()), 3);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertInstanceOf(TaskTimeoutException::class, $task->getError());
        }
    }

    public function testSyncTask(): void
    {
        $task = new SyncTask();
        $this->taskmaster->runTask($task);
        $this->taskmaster->wait();
        $this->assertFalse($task->getResult());
    }

    public function testRecoverAfterTimeout(): void
    {
        $this->taskmaster->setDefaultTaskTimeout(0.005 * $this->getTimeFactor());
        $this->addTasks(new InterruptableSleepTask(10_000 * $this->getTimeFactor()), 3);
        $this->addTasks(new EmptyTask(), 3);

        $counter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $counter++;
            if ($task instanceof InterruptableSleepTask) {
                $this->assertInstanceOf(TaskTimeoutException::class, $task->getError());
            } else {
                $this->assertNull($task->getError());
            }
        }
        $this->assertEquals(6, $counter);
    }

    public function testInitTask(): void
    {
        $this->taskmaster = new Taskmaster();
        $this->taskmaster->setDefaultInitTask(InitTask::class);
        $this->taskmaster->addWorkers($this->createWorker(), 3);

        $this->addTasks(new InitValidateTask(), 3);
        $counter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $this->assertNull($task->getError());
            $this->assertTrue($task->getResult());
            $counter++;
        }
        $this->assertEquals(3, $counter);
    }

    public function testInitTaskAfterWorkerCreation(): void
    {
        $this->taskmaster = new Taskmaster();
        $this->taskmaster->addWorkers($this->createWorker(), 3);
        $this->taskmaster->setDefaultInitTask(InitTask::class);

        $this->addTasks(new InitValidateTask(), 3);
        $counter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $this->assertNull($task->getError());
            $this->assertTrue($task->getResult());
            $counter++;
        }
        $this->assertEquals(3, $counter);
    }
}