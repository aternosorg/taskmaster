<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;

class ErrorTask extends SuppressedErrorOutputTask
{
    /**
     * @param string $message
     */
    #[OnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @return void
     */
    #[OnChild]
    public function run(): void
    {
        trigger_error($this->message, E_USER_ERROR);
    }
}