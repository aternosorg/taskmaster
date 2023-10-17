<?php

namespace Aternos\Taskmaster\Communication;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Closure;

/**
 * Interface CommunicatorInterface
 *
 * Interface for all communicators, e.g. worker instances, runtimes or proxies.
 * Can be implemented using the {@link RequestHandlingTrait} and {@link SocketCommunicatorTrait}.
 *
 * @package Aternos\Taskmaster\Communication
 */
interface CommunicatorInterface
{
    /**
     * Register a request handler for a specific request class
     *
     * The handler will be called when a request of the given class is received.
     * The handler gets the {@link RequestInterface} as first parameter and should return a
     * {@link ResponseInterface} directly or any value that will be wrapped in a response.
     * It should be possible to register multiple handlers for different request classes.
     * Only the first handler that matches the request class will be called.s
     *
     * @param string $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerRequestHandler(string $requestClass, Closure $handler): static;

    /**
     * Register a handler for a specific request class that is called after the response is sent
     *
     * The handler gets the {@link RequestInterface} as first parameter.
     * It should be possible to register multiple handlers for the same request class.
     * The handlers will be called in the order they were registered.
     *
     * @param string $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerAfterRequestHandler(string $requestClass, Closure $handler): static;

    /**
     * Send an async request and return a response promise
     *
     * @param RequestInterface $request
     * @return ResponsePromise
     */
    public function sendRequest(RequestInterface $request): ResponsePromise;
}