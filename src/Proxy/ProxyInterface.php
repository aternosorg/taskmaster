<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;

interface ProxyInterface
{
    public function setOptions(TaskmasterOptions $options): static;

    public function start(): static;

    public function stop(): static;

    public function update(): static;

    public function startWorker(ProxyableWorkerInstanceInterface $worker): ResponsePromise;

    public function stopWorker(ProxyableWorkerInstanceInterface $worker): ResponsePromise;

    public function getSocket(): SocketInterface;

    public function getProxySocket(): ProxySocketInterface;
}