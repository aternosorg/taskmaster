<?php

namespace Aternos\Taskmaster\Communication;

class Request implements RequestInterface
{
    protected ?string $requestId = null;

    public function __construct()
    {
        $this->requestId = uniqid();
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}