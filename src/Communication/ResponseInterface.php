<?php

namespace Aternos\Taskmaster\Communication;

interface ResponseInterface
{
    public function getRequestId(): string;
    public function getData(): mixed;
}