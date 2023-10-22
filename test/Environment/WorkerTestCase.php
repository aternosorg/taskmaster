<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Task\Task;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Task\AdditionTask;
use Aternos\Taskmaster\Test\Task\CallbackTask;
use Aternos\Taskmaster\Test\Task\EmptyTask;
use Aternos\Taskmaster\Test\Task\ChildExceptionTask;
use Aternos\Taskmaster\Test\Task\ParentExceptionTask;
use Aternos\Taskmaster\Test\Task\SynchronizedFieldTask;
use Aternos\Taskmaster\Test\Task\UnsynchronizedFieldTask;
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
            $this->taskmaster->addTask($clone);
        }
        return $tasks;
    }

    public function testRunEmptyTask(): void
    {
        $this->taskmaster->addTask(new EmptyTask());
        $this->taskmaster->wait();
        $this->assertTrue(true);
    }

    public function testGetTaskResult(): void
    {
        $task = new AdditionTask(1, 2);
        $this->taskmaster->addTask($task);
        $this->taskmaster->wait();
        $this->assertEquals(3, $task->getResult());
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