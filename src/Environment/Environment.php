<?php

namespace Aternos\Taskmaster\Environment;

use Aternos\Taskmaster\Taskmaster;

abstract class Environment implements EnvironmentInterface
{
    protected ?Taskmaster $taskmaster = null;

    /**
     * @param Taskmaster $taskmaster
     * @return $this
     */
    public function setTaskmaster(Taskmaster $taskmaster): static
    {
        $this->taskmaster = $taskmaster;
        return $this;
    }
}