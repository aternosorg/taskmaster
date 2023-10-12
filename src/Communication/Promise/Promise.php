<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Closure;
use Exception;
use Fiber;
use ReflectionException;
use Throwable;

class Promise
{
    /**
     * @var Closure[]
     */
    protected array $successHandlers = [];

    /**
     * @var Closure[]
     */
    protected array $exceptionHandlers = [];

    /**
     * @var Fiber[]
     */
    protected array $fibers = [];

    protected mixed $value = null;
    protected Exception $exception;
    protected bool $resolved = false;
    protected bool $failed = false;

    /**
     * @param Closure $callback
     * @return $this
     */
    public function then(Closure $callback): static
    {
        if ($this->resolved) {
            $callback($this->value);
            return $this;
        }
        $this->successHandlers[] = $callback;
        return $this;
    }

    /**
     * @param Closure $callback
     * @return $this
     */
    public function catch(Closure $callback): static
    {
        if ($this->failed) {
            $callback($this->exception);
            return $this;
        }
        $this->exceptionHandlers[] = $callback;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     * @throws Throwable
     */
    public function resolve(mixed $value = null): static
    {
        if ($this->resolved) {
            return $this;
        }
        $this->resolved = true;
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
     * @param Exception $exception
     * @return $this
     * @throws Throwable
     */
    public function reject(Exception $exception): static
    {
        if ($this->failed) {
            return $this;
        }
        $this->failed = true;
        $this->exception = $exception;
        foreach ($this->exceptionHandlers as $callback) {
            if (!$this->matchesFirstArgument($callback, $exception)) {
                continue;
            }
            $callback($exception);
        }
        foreach ($this->fibers as $fiber) {
            $fiber->throw($exception);
        }
        return $this;
    }

    /**
     * @throws ReflectionException
     */
    protected function matchesFirstArgument(Closure $callback, Exception $exception): bool
    {
        $reflection = new \ReflectionFunction($callback);
        $parameters = $reflection->getParameters();
        if (count($parameters) === 0) {
            return true;
        }
        $firstArgument = $parameters[0];
        $type = $firstArgument->getType();
        if ($type === null) {
            return true;
        }
        return is_a($exception, $type->getName());
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function wait(): mixed
    {
        if ($this->resolved) {
            return $this->value;
        }
        if ($this->failed) {
            throw $this->exception;
        }
        if (!Fiber::getCurrent()) {
            throw new \RuntimeException("Promise::wait() can only be called from within a fiber");
        }
        $this->fibers[] = Fiber::getCurrent();
        return Fiber::suspend();
    }
}