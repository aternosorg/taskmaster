<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\TaskmasterOptions;

/**
 * Class Proxy
 *
 * Base class for proxies, a proxy can be used to start workers using a different environment, e.g.
 * CLI as base environment.
 *
 * @package Aternos\Taskmaster\Proxy
 */
abstract class Proxy implements ProxyInterface
{
    protected ?TaskmasterOptions $options = null;
    protected ProxyStatus $status = ProxyStatus::CREATED;

    /**
     * @inheritDoc
     */
    public function setOptions(TaskmasterOptions $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOptionsIfNecessary(TaskmasterOptions $options): static
    {
        if ($this->options === null) {
            $this->setOptions($options);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): ProxyStatus
    {
        return $this->status;
    }
}