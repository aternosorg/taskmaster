<?php

namespace Aternos\Taskmaster\Runtime;

interface AsyncRuntimeInterface extends RuntimeInterface
{
    public function start(): void;
}