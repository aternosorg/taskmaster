<?php

namespace Aternos\Taskmaster\Exception;

/**
 * Class PhpError
 *
 * Represents a PHP error, received from an error handler
 *
 * @see https://www.php.net/manual/en/function.set-error-handler.php
 * @package Aternos\Taskmaster\Exception
 */
class PhpError
{
    /**
     * PhpError constructor.
     *
     * @see https://www.php.net/manual/en/function.set-error-handler.php
     * @param int $level
     * @param string $message
     * @param string $file
     * @param int $line
     */
    public function __construct(
        protected int    $level,
        protected string $message,
        protected string $file,
        protected int    $line
    )
    {
    }

    /**
     * Get the error level
     *
     * @see https://www.php.net/manual/en/errorfunc.constants.php
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Get the file in which the error occurred
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get the line in which the error occurred
     *
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * Get the error message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the error level as string
     *
     * @see https://github.com/php/php-src/blob/95f829db13e0ebb5a36844292f6947700fef8811/main/main.c#L1296
     * @return string
     */
    public function getLevelString(): string
    {
        return match ($this->level) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => "Fatal error",
            E_RECOVERABLE_ERROR => "Recoverable fatal error",
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => "Warning",
            E_PARSE => "Parse error",
            E_NOTICE, E_USER_NOTICE => "Notice",
            E_STRICT => "Strict Standards",
            E_DEPRECATED, E_USER_DEPRECATED => "Deprecated",
            default => "Unknown error"
        };
    }

    /**
     * Get whether the error is fatal
     *
     * E_RECOVERABLE_ERROR is considered fatal here, because it would require special handling
     * which is currently not implemented due to a lack of known use cases.
     * Php also considers E_RECOVERABLE_ERROR fatal by default.
     *
     * @return bool
     */
    public function isFatal(): bool
    {
        return in_array($this->level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR]);
    }

    /**
     * Get the full error message like PHP would display it
     *
     * @see https://github.com/php/php-src/blob/95f829db13e0ebb5a36844292f6947700fef8811/main/main.c#L1376
     * @return string
     */
    public function getFullMessage(): string
    {
        return $this->getLevelString() . ": " . $this->getMessage() . " in " . $this->getFile() . " on line " . $this->getLine() . PHP_EOL;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullMessage();
    }
}