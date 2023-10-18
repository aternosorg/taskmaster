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

/**
 * Class Task
 *
 * A task class represents a single task that runs in the child runtime and can
 * communicate back to the parent. A task has to be serializable.
 *
 * This is the base implementation of the {@link TaskInterface} interface which includes
 * more features such as the {@link Task::call()} and {@link Task::callAsync()} methods.
 *
 * @package Aternos\Taskmaster\Task
 */
abstract class Task implements TaskInterface
{
    protected ?RuntimeInterface $runtime = null;
    protected ?string $group = null;
    protected mixed $result = null;
    protected ?ErrorResponse $error = null;

    /**
     * @inheritDoc
     */
    #[RunOnChild]
    public function setRuntime(RuntimeInterface $runtime): static
    {
        $this->runtime = $runtime;
        return $this;
    }

    /**
     * Get the task result after the task has finished
     *
     * The task result is the return value of the {@link Task::run()} method.
     * If the {@link Task::handleResult()} method is overwritten, make sure to
     * call the parent method or set the {@link Task::result} property yourself.
     *
     * @return mixed
     */
    #[RunOnParent]
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Get the task error after the task has finished
     *
     * The task error is an error caused in the {@link Task::run()} method or
     * an unexpected worker exit.
     * If the {@link Task::handleError()} method is overwritten, make sure to
     * call the parent method or set the {@link Task::error} property yourself.
     *
     * @return ErrorResponse|null
     */
    #[RunOnParent]
    public function getError(): ?ErrorResponse
    {
        return $this->error;
    }

    /**
     * Call a function on the parent asynchronously
     *
     * This function can be used to call a function on the parent process asynchronously.
     * The function has to be a public method of the task class and must not be marked with the {@link RunOnChild}
     * attribute.
     *
     * Any {@link Synchronized} fields are synchronized before the function is called and before the response is
     * returned.
     *
     * The asynchronous call returns a {@link ResponseDataPromise} which can be used to wait for the response.
     * The response data is the return value of the called function.
     *
     * You can add any further arguments to the function call as second, third, ... parameter.
     *
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
     * Handle a task response
     *
     * Applies all {@link Synchronized} fields from the response to the task if possible.
     *
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
     * Call a function on the parent and wait for the response
     *
     * This function can be used to call a function on the parent process. The function has to be a public
     * method of the task class and must not be marked with the {@link RunOnChild} attribute.
     *
     * Any {@link Synchronized} fields are synchronized before the function is called and before the response is
     * returned.
     *
     * This function returns the return value of the called function.
     *
     * You can add any further arguments to the function call as second, third, ... parameter.
     *
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
     * @inheritDoc
     */
    #[RunOnParent]
    public function handleResult(mixed $result): void
    {
        $this->result = $result;
    }

    /**
     * @inheritDoc
     */
    #[RunOnParent]
    public function handleError(ErrorResponse $error): void
    {
        $this->error = $error;
        fwrite(STDERR, $error->getError() . PHP_EOL);
    }

    /**
     * @inheritDoc
     */
    #[RunOnChild]
    public function handleUncriticalError(PhpError $error): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[RunOnParent]
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Set the worker group this tasks should be executed on
     *
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