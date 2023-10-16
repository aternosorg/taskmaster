<?php

namespace Aternos\Taskmaster\Communication;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Closure;

interface CommunicatorInterface
{
    /**
     * @param string $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerRequestHandler(string $requestClass, Closure $handler): static;

    /**
     * @param string $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerAfterRequestHandler(string $requestClass, Closure $handler): static;

    /**
     * @param RequestInterface $request
     * @return ResponsePromise
     */
    public function sendRequest(RequestInterface $request): ResponsePromise;
}