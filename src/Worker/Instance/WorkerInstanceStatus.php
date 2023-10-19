<?php

namespace Aternos\Taskmaster\Worker\Instance;

/**
 * Enum WorkerInstanceStatus
 *
 * Represents the status of a worker instance, can be received using {@link WorkerInstanceInterface::getStatus()}.
 *
 * @package Aternos\Taskmaster\Worker
 */
enum WorkerInstanceStatus
{
    /**
     * The {@link WorkerInstanceInterface::update()} method is only called when the
     * worker instance is in one of these states.
     */
    const GROUP_UPDATE = [self::STARTING, self::WORKING, self::IDLE];

    /**
     * The worker instance is created but not started yet.
     * This is the default state.
     */
    case CREATED;

    /**
     * The worker instance was started but is not yet ready to receive tasks.
     */
    case STARTING;

    /**
     * The worker instance is ready to receive tasks.
     */
    case IDLE;

    /**
     * The worker instance is currently working on a task.
     */
    case WORKING;

    /**
     * The worker instance was stopped regularly.
     */
    case FINISHED;

    /**
     * The worker instance was stopped or stopped on its own because of an error.
     */
    case FAILED;
}
