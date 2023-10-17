<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;

class ErrorTask extends SuppressedErrorOutputTask
{
    /**
     * @param string $message
     */
    #[RunOnParent]
    public function __construct(protected string $message)
    {
    }

    /**
     * @return void
     */
    #[RunOnChild]
    public function run(): void
    {
        trigger_error($this->message, E_USER_ERROR);
    }
}