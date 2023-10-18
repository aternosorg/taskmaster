<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Environment\Fork\ForkWorker;
use Aternos\Taskmaster\Environment\Process\ProcessWorker;

/**
 * Class PhpErrorResponse
 *
 * Error response for fatal PHP errors caught by the error handler.
 * Catching fatal errors is only possible in worker runtimes that execute the PHP code in a separate process, e.g. in the
 * {@link ForkWorker} or {@link ProcessWorker}.
 *
 * @package Aternos\Taskmaster\Communication\Response
 */
class PhpFatalErrorResponse extends ErrorResponse
{
    /**
     * @param string $requestId
     * @param PhpError $error
     */
    public function __construct(string $requestId, PhpError $error)
    {
        parent::__construct($requestId, $error);
    }

    /**
     * @return PhpError
     */
    public function getPhpError(): PhpError
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function getError(): string
    {
        return $this->getPhpError()->getFullMessage();
    }
}