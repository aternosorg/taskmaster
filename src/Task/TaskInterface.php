<?php

namespace Aternos\Taskmaster\Task;

interface TaskInterface
{
    public function run(): TaskResult;
}