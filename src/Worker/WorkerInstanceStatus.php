<?php

namespace Aternos\Taskmaster\Worker;

enum WorkerInstanceStatus
{
    const GROUP_UPDATE = [self::STARTING, self::WORKING, self::IDLE];

    case CREATED;
    case STARTING;
    case WORKING;
    case IDLE;
    case FINISHED;
    case FAILED;
}
