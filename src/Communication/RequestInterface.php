<?php

namespace Aternos\Taskmaster\Communication;

interface RequestInterface
{
    public function getRequestId(): string;
}