<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Promise\ResponseDataPromise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Response\ErrorResponse;
use Aternos\Taskmaster\Communication\ResponseInterface;
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
            if ($reflectionFunction->getAttributes(RunOnChild::class)) {
                throw new InvalidArgumentException("You can not call closures with the #[RunOnChild] attribute.");
            }
            $function = $reflectionFunction->getName();
        }

        $request = (new ExecuteFunctionRequest($function, $arguments))->loadFromTask($this);
        $responsePromise = $this->runtime->sendRequest($request)
            ->then($this->handleTaskResponse(...))
            ->catch(fn(\Exception $e, ResponseInterface $response) => $this->handleTaskResponse($response));
        return new ResponseDataPromise($responsePromise);
    }

    /**
     * @param ResponseInterface $response
     * @return void
     */
    protected function handleTaskResponse(ResponseInterface $response): void
    {
        if (!$response instanceof TaskMessageInterface) {
            return;
        }
        $response->applyToTask($this);
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

    public function handleError(ErrorResponse $error): void
    {
        fwrite(STDERR, $error->getError() . PHP_EOL);
    }
}