<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Exception;
use Throwable;

class ParentExceptionTask extends SuppressedErrorOutputTask
{
    #[OnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @throws Exception
     */
    #[OnParent]
    public function throwException(): void
    {
        throw new Exception($this->message);
    }

    /**
     * @throws Throwable
     */
    #[OnChild]
    public function run(): void
    {
        $this->call($this->throwException(...));
    }
}