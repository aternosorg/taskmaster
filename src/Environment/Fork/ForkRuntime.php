<?php

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Communication\ResponsePromise;
use Aternos\Taskmaster\Runtime;

class ForkRuntime extends Runtime
{
    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];

    /**
     * @param resource $socket
     */
    public function __construct(protected mixed $socket)
    {
        parent::__construct();
    }

    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        fwrite($this->socket, serialize($request) . PHP_EOL);
        $promise = new ResponsePromise();
        $this->promises[$request->getRequestId()] = $promise;
        return $promise;
    }

    public function start(): void
    {
        while (true) {
            $this->update();
        }
    }

    public function update(): void
    {
        $raw = fgets($this->socket);
        $message = unserialize($raw);
        //var_dump("Runtime received: " . $raw);
        if ($message instanceof RequestInterface) {
            $response = $this->handleRequest($message);
            fwrite($this->socket, serialize($response) . PHP_EOL);
            return;
        }
        if ($message instanceof ResponseInterface) {
            $this->promises[$message->getRequestId()]->resolve($message);
        }

    }
}