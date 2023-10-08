<?php

namespace Aternos\Taskmaster\Communication;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;

interface CommunicatorInterface
{
    public function registerRequestHandler(string $requestClass, \Closure $handler): static;

    public function sendRequest(RequestInterface $request): ResponsePromise;
}