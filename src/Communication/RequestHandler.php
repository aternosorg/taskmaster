<?php

namespace Aternos\Taskmaster\Communication;

use Closure;

class RequestHandler
{
    public function __construct(
        protected string   $requestClass,
        protected Closure $handler
    )
    {
    }

    /**
     * @return string
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
     * @param RequestInterface $request
     * @return bool
     */
    public function matches(RequestInterface $request): bool
    {
        return $request instanceof $this->requestClass;
    }

    /**
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