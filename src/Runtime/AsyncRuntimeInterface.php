<?php

namespace Aternos\Taskmaster\Runtime;

/**
 * Interface AsyncRuntimeInterface
 *
 * An async runtime is executed in a separate process/thread and can be started.
 * It can be used with {@link RuntimeProcess} to be executed in a separate process.
 *
 * @package Aternos\Taskmaster\Runtime
 */
interface AsyncRuntimeInterface extends RuntimeInterface
{
    /**
     * Start the runtime, e.g. by running the update loop
     *
     * @return void
     */
    public function start(): void;
}