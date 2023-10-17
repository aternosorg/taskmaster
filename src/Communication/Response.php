<?php

namespace Aternos\Taskmaster\Communication;

/**
 * Class Response
 *
 * Basic response class, that contains the matching request id and the response data.
 *
 * @package Aternos\Taskmaster\Communication
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @param string $requestId
     * @param mixed $data
     */
    public function __construct(protected string $requestId, protected mixed $data)
    {
    }

    /**
     * Get the response data
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Get the matching request id
     *
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}