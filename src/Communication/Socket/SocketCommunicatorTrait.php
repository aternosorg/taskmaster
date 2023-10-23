<?php

namespace Aternos\Taskmaster\Communication\Socket;

use Aternos\Taskmaster\Communication\CommunicatorInterface;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Exception;
use Throwable;

/**
 * Trait SocketCommunicatorTrait
 *
 * Trait for all classes that communicate via a socket, e.g. worker instances, runtimes or proxies.
 * This trait works best with the {@link RequestHandlingTrait} to fulfill the {@link CommunicatorInterface} interface.
 *
 * @package Aternos\Taskmaster\Communication\Socket
 */
trait SocketCommunicatorTrait
{
    protected ?SocketInterface $socket = null;

    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];

    /**
     * Send an async request through the socket
     *
     * Implements {@link CommunicatorInterface::sendRequest()}
     *
     * @param RequestInterface $request
     * @return ResponsePromise
     * @throws Throwable
     */
    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        try {
            $this->socket->sendMessage($request);
        } catch (SocketWriteException $e) {
            $this->handleFail($e->getMessage());
        }
        return $this->getPromise($request);
    }

    /**
     * Get the promise for a request
     *
     * Returns an existing promise or creates a new one.
     *
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
     * Update the socket by reading new messages and handling them
     *
     * @return $this
     * @throws Throwable
     */
    public function update(): static
    {
        try {
            foreach ($this->socket->receiveMessages() as $message) {
                if ($message instanceof RequestInterface) {
                    $response = $this->handleRequest($message);
                    if ($response) {
                        $this->socket->sendMessage($response);
                    }
                    $this->handleAfterRequest($message);
                    continue;
                }
                if ($message instanceof ResponseInterface) {
                    $this->promises[$message->getRequestId()]->resolve($message);
                    unset($this->promises[$message->getRequestId()]);
                }
            }
        } catch (SocketReadException $e) {
            $this->handleFail($e->getMessage());
        }
        return $this;
    }

    /**
     * Handle a failed socket communication
     *
     * @param string|Exception|null $reason
     * @return $this
     */
    abstract protected function handleFail(null|string|Exception $reason = null): static;

    /**
     * Handle a request
     *
     * You can use the implementation in {@link RequestHandlingTrait::handleRequest()}.
     *
     * @param RequestInterface $request
     * @return ResponseInterface|null
     */
    abstract protected function handleRequest(RequestInterface $request): ?ResponseInterface;

    /**
     * Handle a request after it has been handled by {@link SocketCommunicatorTrait::handleRequest()}
     *
     * Can be used to do something after a request has sent its response.
     * You can use the implementation in {@link RequestHandlingTrait::handleAfterRequest()}.
     *
     * @param RequestInterface $request
     * @return void
     */
    abstract protected function handleAfterRequest(RequestInterface $request): void;
}