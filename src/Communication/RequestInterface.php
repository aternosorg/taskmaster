<?php

namespace Aternos\Taskmaster\Communication;

/**
 * Interface RequestInterface
 *
 * Interface for requests which is a message that includes a unique request id
 *
 * @package Aternos\Taskmaster\Communication
 */
interface RequestInterface extends MessageInterface
{
    /**
     * Get the unique request id
     *
     * @return string
     */
    public function getRequestId(): string;
}