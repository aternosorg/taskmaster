<?php

namespace Aternos\Taskmaster\Communication\Socket;

interface SelectableSocketInterface
{
    /**
     * @return resource
     */
    public function getSelectableReadStream(): mixed;

    /**
     * @param int $microseconds
     * @return void
     */
    public function waitForNewData(int $microseconds): void;
}