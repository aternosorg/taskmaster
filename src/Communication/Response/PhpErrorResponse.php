<?php

namespace Aternos\Taskmaster\Communication\Response;

class PhpErrorResponse extends ErrorResponse
{
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

    public function getError(): string
    {
        return "PHP Fatal error: " . $this->getErrorString() . " in " . $this->getFile() . " on line " . $this->getLine();
    }
}