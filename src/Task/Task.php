<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Promise\ResponseDataPromise;
use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\Response\ErrorResponse;
use Aternos\Taskmaster\Communication\Response\PhpError;
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
    protected ?string $group = null;
    protected mixed $result = null;
    protected ?ErrorResponse $error = null;

    /**
     * @param RuntimeInterface $runtime
     * @return $this
     */
    #[RunOnChild]
    public function setRuntime(RuntimeInterface $runtime): static
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * @return mixed
     */
    #[RunOnParent]
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * @return ErrorResponse|null
     */
    #[RunOnParent]
    public function getError(): ?ErrorResponse
    {
        return $this->error;
    }

    /**
     * @param string|Closure $function
     * @param mixed ...$arguments
     * @return ResponseDataPromise
     * @throws ReflectionException|Throwable
     */
    #[RunOnChild]
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
    #[RunOnChild]
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
    #[RunOnChild]
    protected function call(string|Closure $function, mixed ...$arguments): mixed
    {
        return $this->callAsync($function, ...$arguments)->wait();
    }

    /**
     * @param mixed $result
     * @return void
     */
    #[RunOnParent]
    public function handleResult(mixed $result): void
    {
        $this->result = $result;
    }

    #[RunOnParent]
    public function handleError(ErrorResponse $error): void
    {
        $this->error = $error;
        fwrite(STDERR, $error->getError() . PHP_EOL);
    }

    #[RunOnChild]
    public function handleUncriticalError(PhpError $error): bool
    {
        return false;
    }

    /**
     * @return string|null
     */
    #[RunOnParent]
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @param string|null $group
     * @return $this
     */
    #[RunOnParent]
    public function setGroup(?string $group): static
    {
        $this->group = $group;
        return $this;
    }
}