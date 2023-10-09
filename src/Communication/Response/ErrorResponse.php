<?php

namespace Aternos\Taskmaster\Communication\Response;

use Aternos\Taskmaster\Communication\Response;

abstract class ErrorResponse extends Response
{
    abstract public function getError(): string;
}