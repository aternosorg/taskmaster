<?php

namespace Aternos\Taskmaster\Environment;

use Aternos\Taskmaster\Worker\WorkerInterface;

interface EnvironmentInterface
{
    public function createWorker(): WorkerInterface;
}