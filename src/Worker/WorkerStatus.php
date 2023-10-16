<?php

namespace Aternos\Taskmaster\Worker;

enum WorkerStatus
{
    case AVAILABLE;
    case WORKING;
}