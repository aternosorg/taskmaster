<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\ProxyableWorkerInterface;
use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ProxyWorker extends Worker
{
    use SocketCommunicatorTrait;

    protected WorkerStatus $status = WorkerStatus::STARTING;
    protected string $id;
    protected ProxyableWorkerInterface $worker;
    protected ProxyInterface $proxy;

    public function __construct(TaskmasterOptions $options)
    {
        parent::__construct($options);
    }

    /**
     * @param ProxyableWorkerInterface $worker
     * @return $this
     */
    public function setWorker(ProxyableWorkerInterface $worker): static
    {
        $this->id = $worker->getId();
        $this->worker = $worker;
        return $this;
    }

    /**
     * @param ProxyInterface $proxy
     * @return $this
     */
    public function setProxy(ProxyInterface $proxy): static
    {
        $this->proxy = $proxy;
        return $this;
    }

    public function start(): Promise
    {
        $promise = new Promise();
        $this->socket = new ProxiedSocket($this->proxy->getProxySocket(), $this->id);

        $this->proxy->startWorker($this->worker)->then(function () use ($promise) {
            $this->worker->setSocket($this->socket);
            $this->worker->init();
            $this->status = WorkerStatus::IDLE;
            $this->worker->setStatus(WorkerStatus::IDLE);
            $promise->resolve();
        });
        return $promise;
    }

    /**
     * @return void
     */
    public function update(): void
    {
        if ($this->getStatus() !== WorkerStatus::STARTING) {
            $this->worker->update();
        }
    }

    public function getStatus(): WorkerStatus
    {
        return $this->worker->getStatus();
    }

    public function runTask(TaskInterface $task): ResponsePromise
    {
        return $this->worker->runTask($task);
    }

    public function stop(): void
    {
        $this->proxy->stopWorker($this->worker);
    }
}