<?php

namespace Aternos\Taskmaster\Environment;

use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\WorkerInterface;

interface EnvironmentInterface
{
    /**
     * @param TaskmasterOptions $options
     * @return $this
     */
    public function setOptions(TaskmasterOptions $options): static;

    public function createWorker(): WorkerInterface;
}