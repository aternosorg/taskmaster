<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Task\TaskMessageInterface;
use Aternos\Taskmaster\Task\TaskMessageTrait;
use Exception;

/**
 * Class ExceptionResponse
 *
 * Error response with an exception as data.
 * This response causes the {@link ResponsePromise} to reject instead of resolve.
 *
 * @package Aternos\Taskmaster\Communication\Response
 */
class ExceptionResponse extends ErrorResponse implements TaskMessageInterface
{
    use TaskMessageTrait;

    /**
     * @param string $requestId
     * @param Exception $exception
     */
    public function __construct(string $requestId, Exception $exception)
    {
        parent::__construct($requestId, $exception);
    }

    /**
     * @return Exception
     */
    public function getException(): Exception
    {
        return $this->getData();
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->getException();
    }
}