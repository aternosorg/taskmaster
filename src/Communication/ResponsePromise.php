<?php

namespace Aternos\Taskmaster\Communication;

use Closure;
use Fiber;
use Throwable;

class ResponsePromise
{
    protected ?ResponseInterface $response = null;
    protected ?Closure $successHandler = null;
    protected ?Fiber $fiber = null;

    public function then(Closure $callback): static
    {
        $this->successHandler = $callback;
        return $this;
    }

    public function resolve(ResponseInterface $response): static
    {
        $this->response = $response;
        $this->successHandler?->call($this, $response);
        $this->fiber?->resume($response);
        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws Throwable
     */
    public function wait(): ResponseInterface
    {
        if ($response = $this->response) {
            return $response;
        }
        if (!Fiber::getCurrent()) {
            throw new \RuntimeException("ResponsePromise::wait() can only be called from within a fiber");
        }
        $this->fiber = Fiber::getCurrent();
        return Fiber::suspend();
    }
}