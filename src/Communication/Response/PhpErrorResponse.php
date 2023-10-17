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
class PhpErrorResponse extends ErrorResponse
{
    /**
     * @param string $requestId
     * @param mixed $data
     * @param int $level
     * @param string $file
     * @param int $line
     */
    public function __construct(string $requestId, mixed $data, protected int $level, protected string $file, protected int $line)
    {
        parent::__construct($requestId, $data);
    }

    /**
     * @return string
     */
    public function getErrorString(): string
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @inheritDoc
     */
    public function getError(): string
    {
        return "PHP Fatal error: " . $this->getErrorString() . " in " . $this->getFile() . " on line " . $this->getLine();
    }
}