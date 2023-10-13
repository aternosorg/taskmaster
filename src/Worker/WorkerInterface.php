<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceInterface;

interface WorkerInterface
{
    public function setTaskmaster(Taskmaster $taskmaster): static;

    public function getInstance(): WorkerInstanceInterface;

    public function update(): static;

    public function stop(): static;

    public function getStatus(): WorkerStatus;

    public function assignTask(TaskInterface $task): static;
}