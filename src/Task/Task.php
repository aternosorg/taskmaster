<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\ResponseDataPromise;
use Aternos\Taskmaster\Runtime\RuntimeInterface;
use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use Throwable;

abstract class Task implements TaskInterface
{
    protected ?RuntimeInterface $runtime = null;

    /**
     * @param RuntimeInterface $runtime
     * @return $this
     */
    public function setRuntime(RuntimeInterface $runtime): static
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * @param string|Closure $function
     * @param mixed ...$arguments
     * @return ResponseDataPromise
     * @throws ReflectionException
     */
    protected function callAsync(string|Closure $function, mixed ...$arguments): ResponseDataPromise
    {
        if ($function instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($function);
            if ($reflectionFunction->getClosureThis() !== $this) {
                throw new InvalidArgumentException("You can only call closures bound to the current object.");
            }
            $function = $reflectionFunction->getName();
        }

        $request = new ExecuteFunctionRequest($function, $arguments);
        return new ResponseDataPromise($this->runtime->sendRequest($request));
    }

    /**
     * @param string|Closure $function
     * @param mixed ...$arguments
     * @return mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function call(string|Closure $function, mixed ...$arguments): mixed
    {
        return $this->callAsync($function, ...$arguments)->wait();
    }

    public function handleResult(mixed $result): void
    {
    }
}