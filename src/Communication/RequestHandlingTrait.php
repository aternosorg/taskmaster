<?php

namespace Aternos\Taskmaster\Communication;

use Closure;

trait RequestHandlingTrait
{
    /**
     * @var array RequestHandler[]
     */
    protected array $requestHandlers = [];
    protected array $afterRequestHandlers = [];

    /**
     * @param string $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerRequestHandler(string $requestClass, Closure $handler): static
    {
        $this->requestHandlers[] = new RequestHandler($requestClass, $handler);
        return $this;
    }

    /**
     * @param string $requestClass
     * @param Closure $handler
     * @return $this
     */
    public function registerAfterRequestHandler(string $requestClass, Closure $handler): static
    {
        $this->afterRequestHandlers[] = new RequestHandler($requestClass, $handler);
        return $this;
    }

    /**
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