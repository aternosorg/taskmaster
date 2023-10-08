<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\ResponseInterface;
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