<?php

namespace Aternos\Taskmaster\Proxy;

use Aternos\Taskmaster\Communication\Request\StartWorkerInstanceRequest;
use Aternos\Taskmaster\Communication\Request\StopWorkerInstanceRequest;
use Aternos\Taskmaster\Communication\Request\TerminateRequest;
use Aternos\Taskmaster\Communication\Request\WorkerDiedRequest;
use Aternos\Taskmaster\Communication\RequestHandlingTrait;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketReadException;
use Aternos\Taskmaster\Communication\Socket\Exception\SocketWriteException;
use Aternos\Taskmaster\Communication\Socket\SelectableSocketInterface;
use Aternos\Taskmaster\Communication\Socket\SocketCommunicatorTrait;
use Aternos\Taskmaster\Runtime\AsyncRuntimeInterface;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\ProxyableWorkerInstanceInterface;
use Aternos\Taskmaster\Worker\Instance\SocketWorkerInstanceInterface;
use Exception;

/**
 * Class ProxyRuntime
 *
 * The runtime of the {@link ProcessProxy}, starting worker instances and proxying messages between them.
 *
 * @package Aternos\Taskmaster\Proxy
 */
class ProxyRuntime implements AsyncRuntimeInterface
{
    use SocketCommunicatorTrait;
    use RequestHandlingTrait;

    /**
     * @var ProxyableWorkerInstanceInterface[]
     */
    protected array $workers = [];

    protected ProxySocketInterface $proxySocket;

    /**
     * ProxyRuntime constructor.
     *
     * Opens a socket to php://fd/3 to communicate with the parent process
     */
    public function __construct()
    {
        $this->proxySocket = new ProxySocket(fopen("php://fd/3", ""));
        $this->socket = new ProxiedSocket($this->proxySocket, null);
        $this->registerRequestHandler(StartWorkerInstanceRequest::class, $this->handleWorkerStart(...));
        $this->registerRequestHandler(StopWorkerInstanceRequest::class, $this->handleWorkerStop(...));
        $this->registerRequestHandler(TerminateRequest::class, $this->handleTerminate(...));
    }

    /**
     * Starts a worker instance and registers it in the runtime
     *
     * @param StartWorkerInstanceRequest $request
     * @return void
     */
    protected function handleWorkerStart(StartWorkerInstanceRequest $request): void
    {
        $worker = $request->getWorker();
        $worker->start();
        $this->workers[$worker->getId()] = $worker;
    }

    /**
     * Stops all worker instances, closes the proxy socket and exits the runtime
     *
     * @return void
     */
    protected function handleTerminate(): void
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
        $this->proxySocket->close();
        exit(0);
    }

    /**
     * Stops a worker instance and unregisters it from the runtime
     *
     * @param StopWorkerInstanceRequest $request
     * @return void
     */
    protected function handleWorkerStop(StopWorkerInstanceRequest $request): void
    {
        foreach ($this->workers as $worker) {
            if ($worker->getId() === $request->getWorkerId()) {
                $worker->stop();
                unset($this->workers[$worker->getId()]);
                return;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function start(): void
    {
        while (true) {
            $this->update();
            $this->pipe();
            $this->waitForNewData();
        }
    }

    /**
     * Wait for new data on any socket using {@link stream_select()}
     *
     * @return void
     */
    protected function waitForNewData(): void
    {
        $streams = $this->getSelectableStreams();
        if (count($streams) === 0) {
            usleep(Taskmaster::SOCKET_WAIT_TIME);
            return;
        }
        stream_select($streams, $write, $except, 0, Taskmaster::SOCKET_WAIT_TIME);

    }

    /**
     * Get all streams that can be selected for reading in {@link stream_select()}
     *
     * @return resource[]
     */
    protected function getSelectableStreams(): array
    {
        $streams = [];
        foreach ($this->workers as $worker) {
            if (!$worker instanceof SocketWorkerInstanceInterface) {
                continue;
            }
            $socket = $worker->getSocket();
            if (!$socket) {
                continue;
            }
            if (!$socket instanceof SelectableSocketInterface) {
                continue;
            }
            $stream = $socket->getSelectableReadStream();
            if (is_resource($stream) && !feof($stream)) {
                $streams[] = $stream;
            }
        }
        $streams[] = $this->proxySocket->getSelectableReadStream();
        return $streams;
    }

    /**
     * Pipe messages between the proxy socket and the worker sockets
     *
     * Also checks if the worker sockets have died calls {@link ProxyRuntime::handleWorkerDeath()}.
     *
     * @return void
     * @throws SocketException
     */
    protected function pipe(): void
    {
        foreach ($this->proxySocket->getUnhandledMessages() as $message) {
            $this->sendMessageToWorker($message);
        }
        $this->proxySocket->clearUnhandledMessages();

        foreach ($this->workers as $worker) {
            $this->receiveMessagesFromWorker($worker);
            if ($worker->hasDied()) {
                $this->handleWorkerDeath($worker, "Worker died unexpectedly.");
            }
        }
    }

    /**
     * Send a proxy message to a worker
     *
     * The raw serialized message is sent to the worker socket to avoid unnecessary serialization and deserialization.
     *
     * @param ProxyMessage $message
     * @return void
     * @throws SocketException
     */
    protected function sendMessageToWorker(ProxyMessage $message): void
    {
        $worker = $this->workers[$message->getId()] ?? null;
        if (!$worker) {
            return;
        }
        try {
            $this->workers[$message->getId()]?->getSocket()->sendRaw($message->getMessageString());
        } catch (SocketWriteException $e) {
            $this->handleWorkerDeath($worker, $e->getMessage());
        }
    }

    /**
     * Receive messages from a worker and send them to the proxy socket
     *
     * The raw serialized message is wrapped in a {@link ProxyMessage} and sent to the proxy socket to avoid
     * unnecessary serialization and deserialization.
     *
     * @param ProxyableWorkerInstanceInterface $worker
     * @return void
     * @throws SocketException
     */
    protected function receiveMessagesFromWorker(ProxyableWorkerInstanceInterface $worker): void
    {
        try {
            foreach ($worker->getSocket()->receiveRaw() as $data) {
                $this->proxySocket->sendProxyMessage($worker->getId(), $data);
            }
        } catch (SocketReadException $e) {
            $this->handleWorkerDeath($worker, $e->getMessage());
        }
    }

    /**
     * Handle the death of a worker
     *
     * @param ProxyableWorkerInstanceInterface $worker
     * @param string|null $reason
     * @return void
     * @throws SocketException
     */
    protected function handleWorkerDeath(ProxyableWorkerInstanceInterface $worker, ?string $reason = null): void
    {
        if (!isset($this->workers[$worker->getId()])) {
            return;
        }
        unset($this->workers[$worker->getId()]);
        $this->receiveMessagesFromWorker($worker);
        $this->proxySocket->sendProxyMessage($worker->getId(), new WorkerDiedRequest($reason));
        $worker->stop();
    }

    /**
     * @inheritDoc
     */
    protected function handleFail(null|string|Exception $reason = null): static
    {
        if ($reason instanceof Exception) {
            $reason = $reason->getMessage();
        }
        fwrite(STDERR, "Proxy runtime failed: " . $reason . PHP_EOL);
        exit(1);
    }
}