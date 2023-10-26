<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Exception\PhpError;
use Aternos\Taskmaster\Task\OnBoth;
use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;

class WarningTask extends Task
{
    #[OnBoth] protected ?PhpError $phpError = null;

    /**
     * @return void
     */
    #[OnChild]
    public function run(): void
    {
        trigger_error("Test", E_USER_WARNING);
    }

    /**
     * @param PhpError $error
     * @return bool
     */
    #[OnChild]
    public function handleUncriticalError(PhpError $error): bool
    {
        $this->phpError = $error;
        return true;
    }

    /**
     * @return PhpError|null
     */
    #[OnParent]
    public function getPhpError(): ?PhpError
    {
        return $this->phpError;
    }
}