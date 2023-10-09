<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Throwable;

class ResponsePromise extends Promise
{
    /**
     * @return ResponseInterface
     * @throws Throwable
     */
    public function wait(): ResponseInterface
    {
        return parent::wait();
    }

    public function resolve(mixed $value = null): static
    {
        if ($value instanceof ExceptionResponse) {
            $this->reject($value->getException());
            return $this;
        }
        return parent::resolve($value);
    }
}