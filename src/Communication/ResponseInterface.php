<?php

namespace Aternos\Taskmaster\Communication;

/**
 * Interface ResponseInterface
 *
 * Interface for response classes, that contain the matching request id and the response data.
 *
 * @package Aternos\Taskmaster\Communication
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Get the matching request id
     *
     * @return string
     */
    public function getRequestId(): string;

    /**
     * Get the response data
     *
     * @return mixed
     */
    public function getData(): mixed;
}