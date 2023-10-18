<?php

namespace Aternos\Taskmaster\Runtime;

interface AsyncRuntimeInterface extends RuntimeInterface
{
    /**
     * Start the runtime, e.g. by running the update loop
     *
     * @return void
     */
    public function start(): void;
}