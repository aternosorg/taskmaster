<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Request\StartWorkerRequest;
use Aternos\Taskmaster\Communication\Request\StopWorkerRequest;
use Aternos\Taskmaster\Communication\Request\TerminateRequest;
use Aternos\Taskmaster\Communication\Request\WorkerDiedRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Communication\Socket\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\SocketWriteException;
use Aternos\Taskmaster\Runtime\AsyncRuntimeInterface;
use Aternos\Taskmaster\Worker\ProxyableWorkerInstanceInterface;

class ProxyRuntime implements AsyncRuntimeInterface
{
    use SocketCommunicatorTrait;
    use RequestHandlingTrait;

    /**
     * @var ProxyableWorkerInstanceInterface[]
     */
    protected array $workers = [];

    protected ProxySocketInterface $proxySocket;

    public function __construct()
    {
        $this->proxySocket = new ProxySocket(fopen("php://fd/3", ""));
        $this->socket = new ProxiedSocket($this->proxySocket, null);
        $this->registerRequestHandler(StartWorkerRequest::class, $this->handleWorkerStart(...));
        $this->registerRequestHandler(StopWorkerRequest::class, $this->handleWorkerStop(...));
        $this->registerRequestHandler(TerminateRequest::class, $this->handleTerminate(...));
    }

    public function handleWorkerStart(StartWorkerRequest $request): void
    {
        $worker = $request->getWorker();
        $worker->start();
        $this->workers[$worker->getId()] = $worker;
    }

    /**
     * @param TerminateRequest $request
     * @return void
     */
    public function handleTerminate(TerminateRequest $request): void
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
        $this->proxySocket->close();
        exit(0);
    }

    public function handleWorkerStop(StopWorkerRequest $request): void
    {
        foreach ($this->workers as $worker) {
            if ($worker->getId() === $request->getWorkerId()) {
                $worker->stop();
                unset($this->workers[$worker->getId()]);
                return;
            }
        }
    }

    public function start(): void
    {
        while (true) {
            $this->update();
            $this->pipe();
        }
    }

    /**
     * @return void
     */
    protected function pipe(): void
    {
        foreach ($this->proxySocket->getUnhandledMessages() as $message) {
            $worker = $this->workers[$message->getId()] ?? null;
            if (!$worker) {
                continue;
            }
            try {
                $this->workers[$message->getId()]?->getSocket()->sendRaw($message->getMessageString());
            } catch (SocketWriteException $e) {
                $this->proxySocket->sendProxyMessage($worker->getId(), new WorkerDiedRequest($e->getMessage()));
                $worker->stop();
                unset($this->workers[$worker->getId()]);
            }
        }
        $this->proxySocket->clearUnhandledMessages();
        foreach ($this->workers as $worker) {
            try {
                foreach ($worker->getSocket()->receiveRaw() as $data) {
                    $this->proxySocket->sendProxyMessage($worker->getId(), $data);
                }
            } catch (SocketReadException $e) {
                $this->proxySocket->sendProxyMessage($worker->getId(), new WorkerDiedRequest($e->getMessage()));
                $worker->stop();
                unset($this->workers[$worker->getId()]);
            }
        }
    }

    /**
     * @param string|null $reason
     * @return $this
     */
    protected function handleFail(?string $reason = null): static
    {
        fwrite(STDERR, "Proxy runtime failed: " . $reason . PHP_EOL);
        exit(1);
    }
}