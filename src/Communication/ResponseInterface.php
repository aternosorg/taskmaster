<?php

namespace Aternos\Taskmaster\Communication;

interface ResponseInterface extends MessageInterface
{
    public function getRequestId(): string;
    public function getData(): mixed;
}