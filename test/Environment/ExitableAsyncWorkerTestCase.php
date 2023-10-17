<?php

namespace Aternos\Taskmaster\Test\Environment;

use Aternos\Taskmaster\Communication\Response\PhpErrorResponse;
use Aternos\Taskmaster\Communication\Response\WorkerFailedResponse;
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
            $this->assertInstanceOf(PhpErrorResponse::class, $error);
            $this->assertEquals("Test", $error->getErrorString());
        }
    }

    public function testUnexpectedExit(): void
    {
        $tasks = $this->addTasks(new ExitTask(), 10);
        $this->taskmaster->wait();
        foreach ($tasks as $task) {
            $error = $task->getError();
            $this->assertInstanceOf(WorkerFailedResponse::class, $error);
        }
    }
}