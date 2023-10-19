<?php

namespace Aternos\Taskmaster\Proxy;

/**
 * Enum ProxyStatus
 *
 * Represents the status of a proxy.
 *
 * @package Aternos\Taskmaster\Proxy
 */
enum ProxyStatus
{
    /**
     * The proxy was created but was not started yet.
     */
    case CREATED;

    /**
     * The proxy was started but is not yet ready.
     */
    case STARTING;

    /**
     * The proxy is ready to receive requests.
     */
    case RUNNING;

    /**
     * The proxy was stopped regularly.
     */
    case STOPPED;

    /**
     * The proxy was stopped or stopped on its own because of an error.
     */
    case FAILED;
}
