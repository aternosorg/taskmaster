<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Communication\CommunicatorInterface;
use Aternos\Taskmaster\Task\TaskInterface;

interface WorkerInterface extends CommunicatorInterface
{
    public function getStatus(): WorkerStatus;

    public function runTask(TaskInterface $task): void;

    public function update(): void;

    public function stop(): void;
}