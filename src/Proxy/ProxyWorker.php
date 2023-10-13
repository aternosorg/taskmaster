<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Instance\WorkerInstance;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ProxyWorker extends WorkerInstance
{
    use SocketCommunicatorTrait;

    protected WorkerStatus $status = WorkerStatus::STARTING;
    protected string $id;
    protected ProxyableWorkerInstanceInterface $worker;
    protected ProxyInterface $proxy;

    public function __construct(TaskmasterOptions $options)
    {
        parent::__construct($options);
    }

    /**
     * @param ProxyableWorkerInstanceInterface $worker
     * @return $this
     */
    public function setWorker(ProxyableWorkerInstanceInterface $worker): static
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
            $this->worker->setStatus(WorkerStatus::STARTING);
            $promise->resolve();
        });
        return $promise;
    }

    /**
     * @return $this
     */
    public function update(): static
    {
        if ($this->getStatus() !== WorkerStatus::CREATED && $this->getStatus() !== WorkerStatus::FAILED) {
            $this->worker->update();
        }
        return $this;
    }

    public function getStatus(): WorkerStatus
    {
        return $this->worker->getStatus();
    }

    public function runTask(TaskInterface $task): ResponsePromise
    {
        return $this->worker->runTask($task);
    }

    /**
     * @return $this
     */
    public function stop(): static
    {
        $this->proxy->stopWorker($this->worker);
        return $this;
    }
}