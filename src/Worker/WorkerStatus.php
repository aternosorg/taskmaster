<?php

namespace Aternos\Taskmaster\Worker;

enum WorkerStatus
{
    case WORKING;
    case IDLE;
    case FINISHED;
    case FAILED;
}
