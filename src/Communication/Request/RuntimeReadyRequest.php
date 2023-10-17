<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;

/**
 * Class RuntimeReadyRequest
 *
 * Sent from the runtime to the worker to signal that the runtime is ready to receive the next task
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
class RuntimeReadyRequest extends Request
{

}