<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\StartWorkerInstanceRequest;
use Aternos\Taskmaster\Communication\Request\StopWorkerInstanceRequest;
use Aternos\Taskmaster\Communication\Request\TerminateRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Task\Task;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;

class ProcessProxy extends Proxy
{
    use RequestHandlingTrait;
    use SocketCommunicatorTrait;

    protected ?ProxySocketInterface $proxySocket = null;

    protected ?RuntimeProcess $process = null;

    /**
     * @param ProxyableWorkerInstanceInterface $worker
     * @return ResponsePromise
     */
    public function startWorkerInstance(ProxyableWorkerInstanceInterface $worker): ResponsePromise
    {
        return $this->sendRequest(new StartWorkerInstanceRequest($worker));
    }

    /**
     * @param ProxyableWorkerInstanceInterface $worker
     * @return ResponsePromise
     */
    public function stopWorkerInstance(ProxyableWorkerInstanceInterface $worker): ResponsePromise
    {
        return $this->sendRequest(new StopWorkerInstanceRequest($worker->getId()));
    }

    /**
     * @return SocketInterface
     */
    public function getSocket(): SocketInterface
    {
        return $this->socket;
    }

    /**
     * @return $this
     */
    public function start(): static
    {
        $this->process = new RuntimeProcess($this->options, ProxyRuntime::class);
        $this->proxySocket = new ProxySocket($this->process->getSocket());
        $this->socket = new ProxiedSocket($this->proxySocket, null);
        return $this;
    }

    /**
     * @return ProxySocketInterface
     */
    public function getProxySocket(): ProxySocketInterface
    {
        return $this->proxySocket;
    }

    public function stop(): static
    {
        $this->sendRequest(new TerminateRequest());
        while ($this->process->isRunning()) {
            usleep(Taskmaster::SOCKET_WAIT_TIME);
        }
        return $this;
    }

    protected function handleFail(?string $reason = null): static
    {
        // TODO: handle proxy fail
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->process?->isRunning() === true;
    }
}