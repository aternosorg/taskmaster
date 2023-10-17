<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Exception;
use Throwable;

class ParentExceptionTask extends SuppressedErrorOutputTask
{
    #[RunOnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @throws Exception
     */
    #[RunOnParent]
    public function throwException(): void
    {
        throw new Exception($this->message);
    }

    /**
     * @throws Throwable
     */
    #[RunOnChild]
    public function run(): void
    {
        $this->call($this->throwException(...));
    }
}