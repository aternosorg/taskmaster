<?php

namespace Aternos\Taskmaster\Worker\Instance;

use Aternos\Taskmaster\Communication\CommunicatorInterface;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Promise\TaskPromise;
use Aternos\Taskmaster\Exception\WorkerFailedException;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\TaskmasterOptions;
use Exception;
use Throwable;

/**
 * Interface WorkerInstanceInterface
 *
 * Interface for all worker instances.
 * A worker instance represents a single worker, e.g. a process or thread and is
 * wrapped by a worker which always holds one or no instance.
 * When a worker instance dies or is stopped, the worker creates a new worker instance.
 *
 * @package Aternos\Taskmaster\Worker\Instance
 */
interface WorkerInstanceInterface extends CommunicatorInterface
{
    /**
     * @param TaskmasterOptions $options
     */
    public function __construct(TaskmasterOptions $options);

    /**
     * Initialize the worker instance, e.g. by defining request handlers
     *
     * This method is called before the worker instance is started, but after any potential serialization.
     * When a worker uses a proxy, the instance is serialized and sent to the proxy. Therefore,
     * the default instance after construction must not include any unserializable objects, e.g. Closures.
     * For a proxied worker, the {@link WorkerInstanceInterface::init()} method is only called on the main process,
     * not on the proxy while the {@link WorkerInstanceInterface::start()} method is only called on the proxy.
     *
     * @return $this
     */
    public function init(): static;

    /**
     * Start the worker instance
     *
     * This method is called when a worker gets a task and has no running instance.
     * For a proxied worker, only the proxy calls this method while the {@link WorkerInstanceInterface::init()} method
     * is only called on the main process.
     *
     * @return $this
     */
    public function start(): static;

    /**
     * Get the worker instance status
     *
     * @return WorkerInstanceStatus
     * @see WorkerInstanceStatus
     */
    public function getStatus(): WorkerInstanceStatus;

    /**
     * Run a task on the worker instance
     *
     * Send the task to the worker instance and return a response promise which is resolved asynchronously.
     *
     * @param TaskInterface $task
     * @return TaskPromise
     */
    public function runTask(TaskInterface $task): TaskPromise;

    /**
     * Update the worker instance, e.g. by reading a socket and checking if the worker is still alive
     *
     * This method is called regularly by the taskmaster, see {@link WorkerInstance::update()}
     * and {@link Taskmaster::update()}.
     *
     * @return $this
     */
    public function update(): static;

    /**
     * Stop the worker instance
     *
     * This might be called after a worker has already died or wasn't started yet.
     *
     * @return $this
     */
    public function stop(): static;

    /**
     * Handle a fail of the worker instance
     *
     * If necessary, it resolves the current task response promise with a {@link WorkerFailedException} or
     * with the exception provided.
     *
     * @param string|Exception|null $reason
     * @return $this
     * @throws Throwable
     */
    public function handleFail(null|string|Exception $reason = null): static;
}