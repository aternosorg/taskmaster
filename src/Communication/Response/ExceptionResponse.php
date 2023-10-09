<?php

namespace Aternos\Taskmaster\Communication\Response;


use Exception;

class ExceptionResponse extends ErrorResponse
{
    public function __construct(string $requestId, Exception $data)
    {
        parent::__construct($requestId, $data);
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