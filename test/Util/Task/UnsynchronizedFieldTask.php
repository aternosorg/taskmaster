<?php

namespace Aternos\Taskmaster\Test\Util\Task;

use Aternos\Taskmaster\Task\OnChild;
use Aternos\Taskmaster\Task\OnParent;
use Aternos\Taskmaster\Task\Task;

class UnsynchronizedFieldTask extends Task
{
    #[OnParent]
    protected \Closure $parentClosure;

    #[OnChild]
    protected \Closure $childClosure;

    #[OnParent]
    public function __construct()
    {
        $this->parentClosure = $this->run(...);
    }

    /**
     * @inheritDoc
     */
    #[OnChild]
    public function run(): void
    {
        $this->childClosure = $this->run(...);
    }
}