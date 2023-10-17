<?php

namespace Aternos\Taskmaster\Communication;

use Closure;

/**
 * Class RequestHandler
 *
 * A request handler is a combination of a request class and a handler function.
 * It's used by the {@link RequestHandlingTrait} to handle requests.
 *
 * @package Aternos\Taskmaster\Communication
 */
class RequestHandler
{
    /**
     * @param class-string<RequestInterface> $requestClass
     * @param Closure $handler
     */
    public function __construct(
        protected string   $requestClass,
        protected Closure $handler
    )
    {
    }

    /**
     * @return class-string<RequestInterface>
     */
    public function getRequestClass(): string
    {
        return $this->requestClass;
    }

    /**
     * @return Closure
     */
    public function getHandler(): Closure
    {
        return $this->handler;
    }

    /**
     * Check if the request matches the request class
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function matches(RequestInterface $request): bool
    {
        return $request instanceof $this->requestClass;
    }

    /**
     * Handle the request by calling the handler function and returning the response
     *
     * Wraps the result of the handler function in a response if necessary.
     *
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    public function handle(RequestInterface $request): ?ResponseInterface
    {
        $result = ($this->handler)($request);
        if ($result instanceof ResponseInterface) {
            return $result;
        }
        return new Response($request->getRequestId(), $result);
    }
}