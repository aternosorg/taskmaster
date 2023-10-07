<?php

namespace Aternos\Taskmaster\Communication;

use Throwable;

trait SocketCommunicatorTrait
{
    /**
     * @var resource
     */
    protected mixed $readSocket;

    /**
     * @var resource
     */
    protected mixed $writeSocket;

    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];

    /**
     * @param RequestInterface $request
     * @return ResponsePromise
     */
    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        fwrite($this->writeSocket, serialize($request) . PHP_EOL);
        $promise = new ResponsePromise();
        $this->promises[$request->getRequestId()] = $promise;
        return $promise;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function update(): void
    {
        $result = fgets($this->readSocket);
        if ($result === false) {
            return;
        }
        $message = unserialize($result);
        if ($message instanceof RequestInterface) {
            $response = $this->handleRequest($message);
            fwrite($this->writeSocket, serialize($response) . PHP_EOL);
            return;
        }
        if ($message instanceof ResponseInterface) {
            $this->promises[$message->getRequestId()]->resolve($message);
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    abstract protected function handleRequest(RequestInterface $request): ?ResponseInterface;
}