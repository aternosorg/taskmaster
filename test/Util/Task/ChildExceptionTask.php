<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Exception;

class ChildExceptionTask extends SuppressedErrorOutputTask
{
    #[OnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @throws Exception
     */
    #[OnChild]
    public function run(): void
    {
        throw new Exception($this->message);
    }
}