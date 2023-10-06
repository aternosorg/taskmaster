<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;

class ExecuteFunctionRequest extends Request
{
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