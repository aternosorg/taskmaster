<?php

namespace Aternos\Taskmaster\Exception;

use Aternos\Taskmaster\Environment\Fork\ForkWorker;
use Aternos\Taskmaster\Environment\Process\ProcessWorker;

/**
 * Class PhpFatalErrorException
 *
 * This exception is thrown / passed to error handlers when a PHP fatal error occurs in a child runtime.
 * Catching fatal errors is only possible in worker runtimes that execute the PHP code in a separate process, e.g. in the
 * {@link ForkWorker} or {@link ProcessWorker}.
 *
 * @package Aternos\Taskmaster\Exception
 */
class PhpFatalErrorException extends TaskmasterException
{
    /**
     * @param PhpError $error
     */
    public function __construct(protected PhpError $error)
    {
        parent::__construct($this->error->getFullMessage());
    }

    /**
     * @return PhpError
     */
    public function getPhpError(): PhpError
    {
        return $this->error;
    }
}