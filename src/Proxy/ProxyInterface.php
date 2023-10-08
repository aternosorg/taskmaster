<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\TaskmasterOptions;
use Aternos\Taskmaster\Worker\ProxyableWorkerInterface;

interface ProxyInterface
{
    public function setOptions(TaskmasterOptions $options): static;

    public function start(): void;

    public function stop(): void;

    public function update(): void;

    public function startWorker(ProxyableWorkerInterface $worker): ResponsePromise;

    public function stopWorker(ProxyableWorkerInterface $worker): ResponsePromise;

    public function getSocket(): SocketInterface;

    public function getProxySocket(): ProxySocketInterface;
}