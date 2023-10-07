<?php

namespace Aternos\Taskmaster\Communication;

use Throwable;

class ResponsePromise extends Promise
{
    /**
     * @return ResponseInterface
     * @throws Throwable
     */
    public function wait(): ResponseInterface
    {
        return parent::wait();
    }
}