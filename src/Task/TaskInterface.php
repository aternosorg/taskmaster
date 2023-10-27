<?php

namespace Aternos\Taskmaster\Task;

use Aternos\Taskmaster\Communication\Promise\TaskPromise;
use Aternos\Taskmaster\Exception\PhpError;
use Aternos\Taskmaster\Exception\PhpFatalErrorException;
use Aternos\Taskmaster\Runtime\RuntimeInterface;
use Exception;

/**
 * Interface TaskInterface
 *
 * A task class represents a single task that runs in the child runtime and can
 * communicate back to the parent. A task has to be serializable.
 *
 * @package Aternos\Taskmaster\Task
 */
interface TaskInterface
{
    /**
     * A callback that is executed when the task is finished.
     *
     * The first argument is the return value of the {@link TaskInterface::run()} method
     * or null if no value was returned.
     *
     * The {@link TaskInterface::handleError()} method is not called when this method is called.
     *
     * @param mixed $result
     * @return void
     */
    #[OnParent]
    public function handleResult(mixed $result): void;

    /**
     * A callback that is executed when the task has a fatal error
     *
     * The first argument is an {@link Exception}.
     * There are different fatal error causes, e.g. a fatal php error, an
     * unexpected worker exit or an uncaught exception.
     *
     * A fatal php error is represented by a {@link PhpFatalErrorException} which contains
     * a {@link PhpError} object. An unexpected worker exit is represented by a {@link WorkerFailedException}.
     *
     * The {@link TaskInterface::handleResult()} method is not called when this method is called.
     *
     * @param Exception $error
     * @return void
     */
    #[OnParent]
    public function handleError(Exception $error): void;

    /**
     * Get the worker group this tasks should be executed on
     *
     * @return string|null
     */
    #[OnParent]
    public function getGroup(): ?string;

    /**
     * Run the task
     *
     * This is the method where your main task logic should be implemented.
     * The return value can later be handled on the parent using the {@link TaskInterface::handleResult()} method.
     *
     * Throwing an exception in this method will result in a fatal error and the {@link TaskInterface::handleError()}
     * method will be called.
     *
     * @return mixed
     * @noinspection PhpMissingReturnTypeInspection No return type is intentional, to allow void as return type in child classes
     */
    #[OnChild]
    public function run();

    /**
     * A callback that is executed in the child runtime when the task has an uncritical error, e.g. a warning
     *
     * The first argument is a {@link PhpError} object.
     *
     * Returning false will cause the default PHP error handling to proceed, see {@link set_error_handler()}.
     *
     * @param PhpError $error
     * @return bool
     */
    #[OnChild]
    public function handleUncriticalError(PhpError $error): bool;

    /**
     * Set the runtime instance
     *
     * This is set in the runtime automatically to allow the task to communicate through the runtime
     * with the parent worker.
     *
     * @param RuntimeInterface $runtime
     * @return $this
     */
    #[OnChild]
    public function setRuntime(RuntimeInterface $runtime): static;

    /**
     * Get the unique promise for this task
     *
     * This promise is resolved when the task is finished or rejected when the task has a fatal error.
     * It should be stored using the #[OnParent] attribute as promises are not serializable.
     *
     * @return TaskPromise
     */
    #[OnParent]
    public function getPromise(): TaskPromise;

    /**
     * Get the task result after the task has finished
     *
     * The task result is the return value of the {@link TaskInterface::run()} method.
     * If the {@link TaskInterface::handleResult()} method is overwritten, make sure to
     * call the parent method or set the {@link TaskInterface::result} property yourself.
     *
     * @return mixed
     */
    #[OnParent]
    public function getResult(): mixed;

    /**
     * Get the task error after the task has finished
     *
     * The task error is an error caused in the {@link TaskInterface::run()} method or
     * an unexpected worker exit.
     * If the {@link TaskInterface::handleError()} method is overwritten, make sure to
     * call the parent method or set the {@link TaskInterface::error} property yourself.
     *
     * @return Exception|null
     */
    #[OnParent]
    public function getError(): ?Exception;

    /**
     * Get the current timeout for this task in seconds
     *
     * Decimals are allowed for sub-second timeouts.
     * 0 means no timeout, null means default timeout should be used
     *
     * @return float|null
     */
    public function getTimeout(): ?float;

    /**
     * Set the timeout for this task in seconds
     *
     * Decimals are allowed for sub-second timeouts.
     * 0 means no timeout, null means default timeout should be used
     *
     * @param float|null $timeout
     * @return $this
     */
    public function setTimeout(?float $timeout): static;

    /**
     * Tell the task that it's being executed in a sync environment
     *
     * Some cases must be handled differently in a sync environment because
     * you are operating on the same and not just equal objects, e.g. you
     * might not want to close file handles that are still used by other tasks.
     *
     * @return $this
     */
    public function setSync(bool $sync = true): static;
}