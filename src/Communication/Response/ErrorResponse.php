<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Communication\Response;
use Aternos\Taskmaster\Task\TaskInterface;

/**
 * Class ErrorResponse
 *
 * Parent class for error responses, {@link TaskInterface::handleError()} always gets an {@link ErrorResponse}
 *
 * @package Aternos\Taskmaster\Communication\Response
 */
abstract class ErrorResponse extends Response
{
    /**
     * Get the error message
     *
     * @return string
     */
    abstract public function getError(): string;
}