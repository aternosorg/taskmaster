<?php

namespace Aternos\Taskmaster\Environment;

use Aternos\Taskmaster\TaskmasterOptions;

abstract class Environment implements EnvironmentInterface
{
    protected ?TaskmasterOptions $options = null;

    /**
     * @param TaskmasterOptions $options
     * @return $this
     */
    public function setOptions(TaskmasterOptions $options): static
    {
        $this->options = $options;
        return $this;
    }
}