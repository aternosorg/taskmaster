<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Exception;

class ChildExceptionTask extends SuppressedErrorOutputTask
{
    #[RunOnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @throws Exception
     */
    #[RunOnChild]
    public function run(): void
    {
        throw new Exception($this->message);
    }
}