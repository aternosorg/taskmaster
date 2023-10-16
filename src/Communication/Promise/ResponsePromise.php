<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Throwable;

class ResponsePromise extends Promise
{
    protected ?ResponseInterface $response = null;

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
        $this->response = $value;
        if ($value instanceof ExceptionResponse) {
            $this->reject($value->getException());
            return $this;
        }
        return parent::resolve($value);
    }

    /**
     * @return ResponseInterface[]|null[]
     */
    protected function getAdditionalRejectArguments(): array
    {
        return [$this->response];
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}