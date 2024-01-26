<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Util\Task\AdditionTask;
use Aternos\Taskmaster\Test\Util\Task\CallbackTask;
use Aternos\Taskmaster\Test\Util\Task\ChildExceptionTask;
use Aternos\Taskmaster\Test\Util\Task\EmptyTask;
use Aternos\Taskmaster\Test\Util\Task\LargeTask;
use Aternos\Taskmaster\Test\Util\Task\ParentExceptionTask;
use Aternos\Taskmaster\Test\Util\Task\SynchronizedFieldTask;
use Aternos\Taskmaster\Test\Util\Task\UnsynchronizedFieldTask;
use Exception;
use PHPUnit\Framework\TestCase;

abstract class WorkerTestCase extends TestCase
{
    protected Taskmaster $taskmaster;

    abstract protected function createTaskmaster(): void;

    protected function setUp(): void
    {
        $this->createTaskmaster();
    }

    /**
     * @param TaskInterface $task
     * @param int $amount
     * @return TaskInterface[]
     */
    protected function addTasks(TaskInterface $task, int $amount): array
    {
        $tasks = [];
        for ($i = 0; $i < $amount; $i++) {
            $clone = clone $task;
            $tasks[] = $clone;
            $this->taskmaster->runTask($clone);
        }
        return $tasks;
    }

    /**
     * @return int
     */
    protected function getTimeFactor(): int
    {
        $env = getenv("TASKMASTER_TEST_TIME_FACTOR");
        if ($env === false) {
            return 1;
        }
        return (int)$env;
    }

    public function testRunEmptyTask(): void
    {
        $this->taskmaster->runTask(new EmptyTask());
        $this->taskmaster->wait();
        $this->assertTrue(true);
    }

    public function testGetTaskResult(): void
    {
        $task = new AdditionTask(1, 2);
        $this->taskmaster->runTask($task);
        $this->taskmaster->wait();
        $this->assertEquals(3, $task->getResult());
    }

    public function testRunLargeTask(): void
    {
        $task = new LargeTask(1_000_000);
        $this->taskmaster->runTask($task);
        $this->taskmaster->wait();
        $this->assertEquals(1_000_000, strlen($task->getResult()));
    }

    public function testGetTaskResultFromPromise(): void
    {
        $task = new AdditionTask(1, 2);
        $this->taskmaster->runTask($task)->then(function (mixed $result, TaskInterface $resultTask) use ($task) {
            $this->assertSame($task, $resultTask);
            $this->assertEquals(3, $result);
        });
        $this->taskmaster->wait();
    }

    public function testRunMultipleTasks(): void
    {
        $tasks = $this->addTasks(new AdditionTask(1, 2), 10);
        $this->assertCount(10, $tasks);
        $this->assertCount(10, $this->taskmaster->getTasks());
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertEquals(3, $task->getResult());
        }
    }

    public function testGetMultipleTasksFromWaitGenerator(): void
    {
        $this->addTasks(new AdditionTask(1, 2), 10);
        $taskCounter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $this->assertEquals(3, $task->getResult());
            $taskCounter++;
        }
        $this->assertEquals(10, $taskCounter);
    }

    public function testParentCallback(): void
    {
        CallbackTask::resetCounter();
        $tasks = $this->addTasks(new CallbackTask(3), 10);
        $this->taskmaster->wait();
        $result = [];
        foreach ($tasks as $task) {
            $result = array_merge($result, $task->getResult());
        }
        $this->assertCount(30, $result);
        for ($i = 0; $i < 30; $i++) {
            $this->assertContains($i, $result);
        }
    }

    public function testSynchronizedField(): void
    {
        $tasks = $this->addTasks(new SynchronizedFieldTask(3), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertEquals(6, $task->getResult());
        }
    }

    public function testUnsynchronizedFields(): void
    {
        $tasks = $this->addTasks(new UnsynchronizedFieldTask(), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertNull($task->getError());
        }
    }

    public function testChildException(): void
    {
        $tasks = $this->addTasks(new ChildExceptionTask("Test"), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $error = $task->getError();
            $this->assertInstanceOf(Exception::class, $error);
            $this->assertEquals("Test", $error->getMessage());
        }
    }

    public function testChildExceptionFromPromise(): void
    {
        $task = new ChildExceptionTask("Test");
        $this->taskmaster->runTask($task)->catch(function (Exception $error, TaskInterface $errorTask) use ($task) {
            $this->assertSame($task, $errorTask);
            $this->assertInstanceOf(Exception::class, $task->getError());
            $this->assertEquals("Test", $error->getMessage());
        });
        $this->taskmaster->wait();
    }

    public function testChildExceptionFromWaitGenerator(): void
    {
        $this->addTasks(new ChildExceptionTask("Test"), 10);
        $taskCounter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $this->assertInstanceOf(Exception::class, $task->getError());
            $this->assertEquals("Test", $task->getError()->getMessage());
            $taskCounter++;
        }
        $this->assertEquals(10, $taskCounter);
    }

    public function testParentException(): void
    {
        $tasks = $this->addTasks(new ParentExceptionTask("Test"), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $error = $task->getError();
            $this->assertInstanceOf(Exception::class, $error);
            $this->assertEquals("Test", $error->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $this->taskmaster->wait()->stop();
    }
}