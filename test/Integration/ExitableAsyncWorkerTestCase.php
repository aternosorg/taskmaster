<?php

namespace Aternos\Taskmaster\Test\Integration;

use Aternos\Taskmaster\Exception\PhpFatalErrorException;
use Aternos\Taskmaster\Exception\WorkerFailedException;
use Aternos\Taskmaster\Test\Util\Task\AdditionTask;
use Aternos\Taskmaster\Test\Util\Task\ErrorTask;
use Aternos\Taskmaster\Test\Util\Task\ExitTask;

abstract class ExitableAsyncWorkerTestCase extends AsyncWorkerTestCase
{
    public function testFatalError(): void
    {
        $tasks = $this->addTasks(new ErrorTask("Test"), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $error = $task->getError();
            $this->assertInstanceOf(PhpFatalErrorException::class, $error);
            $this->assertEquals("Test", $error->getPhpError()->getMessage());
        }
    }

    public function testRecoverAfterFatalError(): void
    {
        $this->addTasks(new ErrorTask("Test"), 3);
        $this->addTasks(new AdditionTask(1, 2), 3);

        $counter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $counter++;
            if ($task instanceof ErrorTask) {
                $error = $task->getError();
                $this->assertInstanceOf(PhpFatalErrorException::class, $error);
                $this->assertEquals("Test", $error->getPhpError()->getMessage());
            } else if ($task instanceof AdditionTask) {
                $this->assertEquals(3, $task->getResult());
            }
        }
        $this->assertEquals(6, $counter);
    }

    public function testUnexpectedExit(): void
    {
        $tasks = $this->addTasks(new ExitTask(), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $error = $task->getError();
            $this->assertInstanceOf(WorkerFailedException::class, $error);
        }
    }

    public function testRecoverAfterUnexpectedExit(): void
    {
        $this->addTasks(new ExitTask(), 3);
        $this->addTasks(new AdditionTask(1, 2), 3);

        $counter = 0;
        foreach ($this->taskmaster->waitAndHandleTasks() as $task) {
            $counter++;
            if ($task instanceof ExitTask) {
                $error = $task->getError();
                $this->assertInstanceOf(WorkerFailedException::class, $error);
            } else if ($task instanceof AdditionTask) {
                $this->assertEquals(3, $task->getResult());
            }
        }
        $this->assertEquals(6, $counter);
    }
}