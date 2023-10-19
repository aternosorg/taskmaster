<?php

namespace Aternos\Taskmaster\Worker;

/**
 * Enum WorkerStatus
 *
 * Represents the status of a worker, can be received using {@link WorkerInterface::getStatus()}.
 *
 * @package Aternos\Taskmaster\Worker
 */
enum WorkerStatus
{
    /**
     * The worker is available and ready to receive tasks.
     */
    case AVAILABLE;

    /**
     * The worker has a task assigned and is either working on it or starting a new
     * instance to work on the task.
     */
    case WORKING;
}