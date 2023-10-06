<?php

namespace Aternos\Taskmaster\Communication;

interface CommunicatorInterface
{
    public function registerRequestHandler(string $requestClass, \Closure $handler): static;

    public function sendRequest(RequestInterface $request): ResponsePromise;
}