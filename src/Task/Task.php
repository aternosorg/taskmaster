<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\ResponsePromise;
use Aternos\Taskmaster\RuntimeInterface;
use Closure;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;

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
     * @return mixed
     * @throws ReflectionException
     */
    protected function call(string|Closure $function, mixed ...$arguments): ResponsePromise
    {
        if ($function instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($function);
            if ($reflectionFunction->getClosureThis() !== $this) {
                throw new InvalidArgumentException("You can only call closures bound to the current object.");
            }
            $function = $reflectionFunction->getName();
        }

        $request = new ExecuteFunctionRequest($function, $arguments);
        return $this->runtime->sendRequest($request);
    }
}