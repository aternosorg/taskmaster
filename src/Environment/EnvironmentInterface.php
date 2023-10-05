<?php

namespace Aternos\Taskmaster\Environment;

use Aternos\Taskmaster\Taskmaster;

interface EnvironmentInterface
{
    public function setTaskmaster(Taskmaster $taskmaster): static;

    public function start(): static;

    public function wait(): static;
}