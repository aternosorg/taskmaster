<?php

namespace Aternos\Taskmaster\Communication;

use Closure;
use Fiber;
use Throwable;

class Promise
{
    /**
     * @var Closure[]
     */
    protected array $successHandlers = [];

    /**
     * @var Fiber[]
     */
    protected array $fibers = [];

    protected mixed $value = null;

    /**
     * @param Closure $callback
     * @return $this
     */
    public function then(Closure $callback): static
    {
        if ($this->value) {
            $callback($this->value);
            return $this;
        }
        $this->successHandlers[] = $callback;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws Throwable
     */
    public function resolve(mixed $value): static
    {
        $this->value = $value;
        foreach ($this->successHandlers as $callback) {
            $callback($value);
        }
        foreach ($this->fibers as $fiber) {
            $fiber->resume($value);
        }
        return $this;
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function wait(): mixed
    {
        if ($this->value !== null) {
            return $this->value;
        }
        if (!Fiber::getCurrent()) {
            throw new \RuntimeException("Promise::wait() can only be called from within a fiber");
        }
        $this->fibers[] = Fiber::getCurrent();
        return Fiber::suspend();
    }
}