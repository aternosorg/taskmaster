<?php

namespace Aternos\Taskmaster\Communication;

use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Closure;

/**
 * Trait RequestHandlingTrait
 *
 * Trait for request handling, can be used to implement the {@link CommunicatorInterface}.
 * Provides functions to register request handlers and handle requests.
 * Works together with the {@link SocketCommunicatorTrait}.
 *
 * @package Aternos\Taskmaster\Communication
 */
trait RequestHandlingTrait
{
    /**
     * @var array RequestHandler[]
     */
    protected array $requestHandlers = [];
    protected array $afterRequestHandlers = [];

    /**
     * Register a request handler for a specific request class that is called when a request is received
     *
     * The handler will be called when a request of the given class is received.
     * The handler gets the {@link RequestInterface} as first parameter and should return a
     * {@link ResponseInterface} directly or any value that will be wrapped in a response.
     *
     * @param class-string<RequestInterface> $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerRequestHandler(string $requestClass, Closure $handler): static
    {
        $this->requestHandlers[] = new RequestHandler($requestClass, $handler);
        return $this;
    }

    /**
     * Register a handler for a specific request class that is called after the response is sent
     *
     * The handler gets the {@link RequestInterface} as first parameter.
     *
     * @param class-string<RequestInterface> $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerAfterRequestHandler(string $requestClass, Closure $handler): static
    {
        $this->afterRequestHandlers[] = new RequestHandler($requestClass, $handler);
        return $this;
    }

    /**
     * Handles a request by calling the first matching request handler
     *
     * Implements the {@link SocketCommunicatorTrait::handleRequest()} method.
     *
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    protected function handleRequest(RequestInterface $request): ?ResponseInterface
    {
        foreach ($this->requestHandlers as $requestHandler) {
            if ($requestHandler->matches($request)) {
                return $requestHandler->handle($request);
            }
        }
        return null;
    }

    /**
     * Handles a request by calling all matching after request handlers
     *
     * Implements the {@link SocketCommunicatorTrait::handleAfterRequest()} method.
     *
     * @param RequestInterface $request
     * @return void
     */
    protected function handleAfterRequest(RequestInterface $request): void
    {
        foreach ($this->afterRequestHandlers as $requestHandler) {
            if ($requestHandler->matches($request)) {
                $requestHandler->handle($request);
            }
        }
    }
}