<?php

namespace Aternos\Taskmaster\Worker;

enum WorkerStatus
{
    case STARTING;
    case WORKING;
    case IDLE;
    case FINISHED;
    case FAILED;
}
