<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Request\StartWorkerRequest;
use Aternos\Taskmaster\Communication\Request\StopWorkerRequest;
use Aternos\Taskmaster\Communication\Request\TerminateRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Runtime\RuntimeProcess;
use Aternos\Taskmaster\Worker\ProxyableWorkerInterface;

class ProcessProxy extends Proxy
{
    use RequestHandlingTrait;
    use SocketCommunicatorTrait;

    protected ?ProxySocketInterface $proxySocket = null;

    protected RuntimeProcess $process;

    /**
     * @param ProxyableWorkerInterface $worker
     * @return ResponsePromise
     */
    public function startWorker(ProxyableWorkerInterface $worker): ResponsePromise
    {
        return $this->sendRequest(new StartWorkerRequest($worker));
    }

    /**
     * @param ProxyableWorkerInterface $worker
     * @return ResponsePromise
     */
    public function stopWorker(ProxyableWorkerInterface $worker): ResponsePromise
    {
        return $this->sendRequest(new StopWorkerRequest($worker->getId()));
    }

    /**
     * @return SocketInterface
     */
    public function getSocket(): SocketInterface
    {
        return $this->socket;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $this->process = new RuntimeProcess($this->options, ProxyRuntime::class);
        $this->proxySocket = new ProxySocket($this->process->getSocket());
        $this->socket = new ProxiedSocket($this->proxySocket, null);
    }

    /**
     * @return ProxySocketInterface
     */
    public function getProxySocket(): ProxySocketInterface
    {
        return $this->proxySocket;
    }

    public function stop(): void
    {
        $this->sendRequest(new TerminateRequest());
        //$this->process->stop();
    }
}