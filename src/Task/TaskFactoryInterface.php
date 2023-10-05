<?php

namespace Aternos\Taskmaster\Task;

interface TaskFactoryInterface
{
    public function createNextTask(): TaskInterface;
}