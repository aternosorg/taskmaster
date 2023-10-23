<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Task\TaskInterface;
use Closure;
use Exception;
use Fiber;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use Throwable;

/**
 * Class Promise
 *
 * Promise implementation, mainly used internally as return value for async functions
 * An async function can immediately return a promise and resolve it later
 * You can add success and exception handlers to the promise using {@see Promise::then()} and {@see Promise::catch()}
 * The promise can be resolved using {@see Promise::resolve()} and rejected using {@see Promise::reject()}
 * If you are in a fiber (e.g. in {@link TaskInterface::run()}), you can wait for the promise to resolve or throw using {@see Promise::wait()}.
 *
 * @package Aternos\Taskmaster\Communication\Promise
 */
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
     * Add a success handler to the promise, the promise result will be passed as first argument to the callback
     *
     * Success handlers are called in the order they were added.
     *
     * @param Closure $callback
     * @return $this
     */
    public function then(Closure $callback): static
    {
        if ($this->resolved) {
            $callback($this->value, ...$this->getAdditionalResolveArguments());
            return $this;
        }
        $this->successHandlers[] = $callback;
        return $this;
    }

    /**
     * Add an exception handler to the promise, the exception will be passed as first argument to the callback
     *
     * Exception handlers can define which exception types they want to handle by adding a type hint to the first argument.
     * If the exception does not match the type hint, the handler will not be called.
     * If no type hint is defined, the handler will be called for all exceptions.
     * Exception handlers are called in the order they were added.
     *
     * @param Closure $callback
     * @return $this
     */
    public function catch(Closure $callback): static
    {
        if ($this->failed) {
            $callback($this->exception, ...$this->getAdditionalRejectArguments());
            return $this;
        }
        $this->exceptionHandlers[] = $callback;
        return $this;
    }

    /**
     * Resolve the promise
     *
     * A promise can only be resolved once.
     * All success handlers will be called with the given value.
     * After that all waiting fibers will be resumed with the given value.
     *
     * @param mixed $value
     * @return $this
     * @throws Throwable
     */
    public function resolve(mixed $value = null): static
    {
        if ($this->resolved || $this->failed) {
            return $this;
        }
        $this->resolved = true;
        $this->value = $value;
        foreach ($this->successHandlers as $callback) {
            $callback($value, ...$this->getAdditionalResolveArguments());
        }
        foreach ($this->fibers as $fiber) {
            $fiber->resume($value);
        }
        return $this;
    }

    /**
     * Reject the promise with an exception
     *
     * A promise can only be rejected once.
     * Matching exception handlers will be called with the given exception, see {@see Promise::catch()}.
     * After that all waiting fibers will be resumed by throwing the given exception.
     *
     * @param Exception $exception
     * @return $this
     * @throws Throwable
     */
    public function reject(Exception $exception): static
    {
        if ($this->failed || $this->resolved) {
            return $this;
        }
        $this->failed = true;
        $this->exception = $exception;
        foreach ($this->exceptionHandlers as $callback) {
            if (!$this->matchesFirstArgument($callback, $exception)) {
                continue;
            }
            $callback($exception, ...$this->getAdditionalRejectArguments());
        }
        foreach ($this->fibers as $fiber) {
            $fiber->throw($exception);
        }
        return $this;
    }

    /**
     * Check if the exception matches the first argument type hint of the given callback
     *
     * @throws ReflectionException
     */
    protected function matchesFirstArgument(Closure $callback, Exception $exception): bool
    {
        $reflection = new ReflectionFunction($callback);
        $parameters = $reflection->getParameters();
        if (count($parameters) === 0) {
            return true;
        }
        $firstArgument = $parameters[0];
        $type = $firstArgument->getType();
        if ($type === null) {
            return true;
        }
        return $this->matchesType($exception, $type);
    }

    /**
     * Check if the given object matches the given type
     *
     * Resolves union and intersection types recursively
     *
     * @param Exception $object
     * @param ReflectionType $type
     * @return bool
     */
    protected function matchesType(Exception $object, ReflectionType $type): bool
    {
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($this->matchesType($object, $unionType)) {
                    return true;
                }
            }
            return false;
        }

        if ($type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $intersectionType) {
                if (!$this->matchesType($object, $intersectionType)) {
                    return false;
                }
            }
            return true;
        }

        if ($type instanceof \ReflectionNamedType) {
            if ($type->getName() === "mixed" || $type->getName() === "object") {
                return true;
            }

            return is_a($object, $type->getName());
        }

        return false;
    }

    /**
     * Wait for the promise to resolve or throw
     *
     * This method can only be called from within a fiber, e.g. in {@link TaskInterface::run()}.
     *
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
            throw new RuntimeException("Promise::wait() can only be called from within a fiber");
        }
        $this->fibers[] = Fiber::getCurrent();
        return Fiber::suspend();
    }

    /**
     * Get additional arguments for success handlers
     *
     * @return array
     */
    protected function getAdditionalResolveArguments(): array
    {
        return [];
    }

    /**
     * Get additional arguments for exception handlers
     *
     * @return array
     */
    protected function getAdditionalRejectArguments(): array
    {
        return [];
    }
}