<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Test\Task\AdditionTask;
use Aternos\Taskmaster\Test\Task\CallbackTask;
use Aternos\Taskmaster\Test\Task\EmptyTask;
use Aternos\Taskmaster\Test\Task\ExceptionTask;
use Aternos\Taskmaster\Test\Task\SynchronizedFieldTask;
use PHPUnit\Framework\TestCase;

abstract class WorkerTestCase extends TestCase
{
    protected Taskmaster $taskmaster;

    abstract protected function createTaskmaster(): void;

    protected function setUp(): void
    {
        $this->createTaskmaster();
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
        $this->assertEquals(3, $task->getExpectedResult());
        $this->assertEquals(3, $task->getResult());
    }

    public function testRunMultipleTasks(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $task = new AdditionTask($i, $i + 1);
            $tasks[] = $task;
            $this->taskmaster->addTask($task);
        }
        $this->assertCount(10, $tasks);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertEquals($task->getExpectedResult(), $task->getResult());
        }
    }

    public function testParentCallback(): void
    {
        CallbackTask::resetCounter();
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $task = new CallbackTask(3);
            $tasks[] = $task;
            $this->taskmaster->addTask($task);
        }
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
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $task = new SynchronizedFieldTask(3);
            $tasks[] = $task;
            $this->taskmaster->addTask($task);
        }
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertEquals(6, $task->getResult());
        }
    }

    public function testChildException(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $task = new ExceptionTask("Test");
            $tasks[] = $task;
            $this->taskmaster->addTask($task);
        }
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $this->assertInstanceOf(\Exception::class, $task->getResult());
            $this->assertEquals("Test", $task->getResult()->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $this->taskmaster->wait()->stop();
    }
}