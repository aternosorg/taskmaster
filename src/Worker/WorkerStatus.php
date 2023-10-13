<?php

namespace Aternos\Taskmaster\Worker;

enum WorkerStatus
{
    case CREATED;
    case STARTING;
    case WORKING;
    case IDLE;
    case FINISHED;
    case FAILED;
}
