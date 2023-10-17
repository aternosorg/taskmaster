<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Task\Task;

/**
 * Class ExecuteFunctionRequest
 *
 * Sent from the runtime to the worker to execute a function, see {@link Task::callAsync()}.
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
class ExecuteFunctionRequest extends TaskRequest
{
    /**
     * @param string $function
     * @param array $arguments
     */
    public function __construct(protected string $function, protected array $arguments = [])
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}