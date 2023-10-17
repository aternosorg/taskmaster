<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Communication\Response\ErrorResponse;
use Aternos\Taskmaster\Communication\Response\ExceptionResponse;
use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Task;
use Exception;

class ExceptionTask extends Task
{
    #[RunOnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @throws Exception
     */
    #[RunOnChild]
    public function run(): mixed
    {
        throw new Exception($this->message);
    }

    #[RunOnParent]
    public function handleError(ErrorResponse $error): void
    {
        if ($error instanceof ExceptionResponse) {
            $this->result = $error->getException();
        }
    }
}