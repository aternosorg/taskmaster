<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\CommunicatorInterface;
use Aternos\Taskmaster\Communication\ResponsePromise;
use Aternos\Taskmaster\Task\TaskInterface;

interface WorkerInterface extends CommunicatorInterface
{
    public function getStatus(): WorkerStatus;

    public function runTask(TaskInterface $task): ResponsePromise;

    public function update(): void;

    public function stop(): void;
}