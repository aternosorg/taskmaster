<?php

namespace Aternos\Taskmaster\Communication;

interface RequestInterface extends MessageInterface
{
    public function getRequestId(): string;
}