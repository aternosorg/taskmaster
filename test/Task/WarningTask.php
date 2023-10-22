<?php

namespace Aternos\Taskmaster\Test\Task;

use Aternos\Taskmaster\Exception\PhpError;
use Aternos\Taskmaster\Task\RunOnChild;
use Aternos\Taskmaster\Task\RunOnParent;
use Aternos\Taskmaster\Task\Synchronized;
use Aternos\Taskmaster\Task\Task;

class WarningTask extends Task
{
    #[Synchronized] protected ?PhpError $phpError = null;

    /**
     * @return void
     */
    #[RunOnChild]
    public function run(): void
    {
        trigger_error("Test", E_USER_WARNING);
    }

    /**
     * @param PhpError $error
     * @return bool
     */
    #[RunOnChild]
    public function handleUncriticalError(PhpError $error): bool
    {
        $this->phpError = $error;
        return true;
    }

    /**
     * @return PhpError|null
     */
    #[RunOnParent]
    public function getPhpError(): ?PhpError
    {
        return $this->phpError;
    }
}