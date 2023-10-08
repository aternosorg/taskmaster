<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\TaskmasterOptions;

abstract class Proxy implements ProxyInterface
{
    protected TaskmasterOptions $options;

    public function setOptions(TaskmasterOptions $options): static
    {
        $this->options = $options;
        return $this;
    }
}