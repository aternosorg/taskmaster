<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Throwable;

trait SocketCommunicatorTrait
{
    protected ?SocketInterface $socket = null;

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
        $this->socket->sendMessage($request);
        return $this->getPromise($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponsePromise
     */
    protected function getPromise(RequestInterface $request): ResponsePromise
    {
        $id = $request->getRequestId();
        if (!isset($this->promises[$id])) {
            $this->promises[$id] = new ResponsePromise();
        }
        return $this->promises[$id];
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function update(): void
    {
        foreach ($this->socket->receiveMessages() as $message) {
            if ($message instanceof RequestInterface) {
                $response = $this->handleRequest($message);
                if ($response) {
                    $this->socket->sendMessage($response);
                }
                continue;
            }
            if ($message instanceof ResponseInterface) {
                $this->promises[$message->getRequestId()]->resolve($message);
            }
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    abstract protected function handleRequest(RequestInterface $request): ?ResponseInterface;
}