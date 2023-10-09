<?php

namespace Aternos\Taskmaster\Communication;

class Response extends Message implements ResponseInterface
{
    public function __construct(protected string $requestId, protected mixed $data)
    {
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }
}