<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;

/**
 * Class ResponsePromise
 *
 * Promise adjusted for response handling.
 * Rejects with the exception from the response if the response is an {@link ExceptionResponse}.
 * Also adds the response as second argument in catch handlers.
 *
 * @package Aternos\Taskmaster\Communication\Promise
 */
class ResponsePromise extends Promise
{
    protected ?ResponseInterface $response = null;

    /**
     * @inheritDoc
     */
    public function resolve(mixed $value = null): static
    {
        $this->response = $value;
        if ($value instanceof ExceptionResponse) {
            $this->reject($value->getException());
            return $this;
        }
        return parent::resolve($value);
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalRejectArguments(): array
    {
        return [$this->response];
    }

    /**
     * Get the response, can also be retrieved in an exception handler as second argument
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}