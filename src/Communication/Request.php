<?php

namespace Aternos\Taskmaster\Communication;

/**
 * Class Request
 *
 * Base class for requests which is a message that includes a unique request id
 *
 * @package Aternos\Taskmaster\Communication
 */
class Request extends Message implements RequestInterface
{
    protected string $requestId;

    /**
     * Request constructor.
     *
     * Generates a unique request id.
     */
    public function __construct()
    {
        $this->requestId = uniqid();
    }

    /**
     * Get the unique request id
     *
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
