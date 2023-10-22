<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Exception\PhpFatalErrorException;
use Aternos\Taskmaster\Exception\WorkerFailedException;
use Aternos\Taskmaster\Test\Task\ErrorTask;
use Aternos\Taskmaster\Test\Task\ExitTask;

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

    public function testUnexpectedExit(): void
    {
        $tasks = $this->addTasks(new ExitTask(), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $error = $task->getError();
            $this->assertInstanceOf(WorkerFailedException::class, $error);
        }
    }
}