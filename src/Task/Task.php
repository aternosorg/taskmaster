<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Promise\ResponseDataPromise;
use Aternos\Taskmaster\Communication\Promise\TaskPromise;
use Aternos\Taskmaster\Communication\Request\ExecuteFunctionRequest;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Exception\PhpError;
use Aternos\Taskmaster\Runtime\RuntimeInterface;
use Closure;
use Exception;
use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionObject;
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
    #[OnChild] protected ?RuntimeInterface $runtime = null;
    #[OnParent] protected ?string $group = null;
    #[OnParent] protected mixed $result = null;
    #[OnParent] protected ?Exception $error = null;
    #[OnParent] protected ?TaskPromise $promise = null;
    #[OnParent] protected ?float $timeout = null;
    #[OnChild] protected bool $sync = false;

    /**
     * @inheritDoc
     */
    #[OnChild]
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
    #[OnParent]
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
     * @return Exception|null
     */
    #[OnParent]
    public function getError(): ?Exception
    {
        return $this->error;
    }

    /**
     * Call a function on the parent asynchronously
     *
     * This function can be used to call a function on the parent process asynchronously.
     * The function has to be a public method of the task class and must not be marked with the {@link OnChild}
     * attribute.
     *
     * Any fields are synchronized before the function is called and before the response is returned.
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
    #[OnChild]
    protected function callAsync(string|Closure $function, mixed ...$arguments): ResponseDataPromise
    {
        if ($function instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($function);
            if ($reflectionFunction->getClosureThis() !== $this) {
                throw new InvalidArgumentException("You can only call closures bound to the current object.");
            }
            if ($reflectionFunction->getAttributes(OnChild::class)) {
                throw new InvalidArgumentException("You can not call closures with the #[OnChild] attribute.");
            }
            $function = $reflectionFunction->getName();
        }

        $request = (new ExecuteFunctionRequest($function, $arguments))->loadFromTask($this);
        $responsePromise = $this->runtime->sendRequest($request)
            ->then($this->handleTaskResponse(...))
            ->catch(fn(Exception $e, ResponseInterface $response) => $this->handleTaskResponse($response));
        return new ResponseDataPromise($responsePromise);
    }

    /**
     * Handle a task response
     *
     * Applies all synchronized fields from the response to the task if possible.
     *
     * @param ResponseInterface $response
     * @return void
     */
    #[OnChild]
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
     * method of the task class and must not be marked with the {@link OnChild} attribute.
     *
     * Any fields are synchronized before the function is called and before the response is returned.
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
    #[OnChild]
    protected function call(string|Closure $function, mixed ...$arguments): mixed
    {
        return $this->callAsync($function, ...$arguments)->wait();
    }

    /**
     * @inheritDoc
     */
    #[OnParent]
    public function handleResult(mixed $result): void
    {
        $this->result = $result;
    }

    /**
     * @inheritDoc
     */
    #[OnParent]
    public function handleError(Exception $error): void
    {
        $this->error = $error;
        fwrite(STDERR, $error->getMessage() . PHP_EOL);
    }

    /**
     * @inheritDoc
     */
    #[OnChild]
    public function handleUncriticalError(PhpError $error): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[OnParent]
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
    #[OnParent]
    public function setGroup(?string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Filter the task properties that should be serialized
     *
     * This method is called when the task is serialized and sent to the child process.
     * It removes all properties that are marked with the {@link OnParent} attribute.
     *
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.serialize
     * @return array
     */
    public function __serialize(): array
    {
        $reflectionObject = new ReflectionObject($this);
        $serializedData = [];
        foreach ($reflectionObject->getProperties() as $property) {
            if ($property->isStatic() || !$property->isInitialized($this)) {
                continue;
            }
            if ($property->getAttributes(OnParent::class)) {
                continue;
            }
            $name = $property->getName();
            $serializedData[$name] = $property->getValue($this);
        }
        return $serializedData;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    #[OnParent]
    public function getPromise(): TaskPromise
    {
        if ($this->promise === null) {
            $this->promise = new TaskPromise($this);
        }
        return $this->promise;
    }

    /**
     * @inheritDoc
     */
    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    /**
     * @inheritDoc
     */
    public function setTimeout(?float $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSync(bool $sync = true): static
    {
        $this->sync = $sync;
        return $this;
    }

    /**
     * Check if the task is executed in a sync environment
     *
     * Some cases must be handled differently in a sync environment because
     * you are operating on the same and not just equal objects, e.g. you
     * might not want to close file handles that are still used by other tasks.
     *
     * @return bool
     */
    protected function isSync(): bool
    {
        return $this->sync;
    }
}