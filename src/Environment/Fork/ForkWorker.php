<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Communication\RequestInterface;
use Aternos\Taskmaster\Communication\ResponseInterface;
use Aternos\Taskmaster\Communication\ResponsePromise;
use Aternos\Taskmaster\Worker\Worker;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ForkWorker extends Worker
{
    /**
     * @var resource
     */
    protected mixed $socket;

    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];

    protected int $pid;

    public function __construct()
    {
        parent::__construct();
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \RuntimeException("Could not fork");
        }
        if ($pid === 0) {
            fclose($sockets[0]);
            $runtime = new ForkRuntime($sockets[1]);
            $runtime->start();
            exit(0);
        }
        fclose($sockets[1]);
        $this->socket = $sockets[0];
        $this->pid = $pid;
        stream_set_blocking($this->socket, false);
    }

    public function sendRequest(RequestInterface $request): ResponsePromise
    {
        fwrite($this->socket, serialize($request) . PHP_EOL);
        $promise = new ResponsePromise();
        $this->promises[$request->getRequestId()] = $promise;
        return $promise;
    }

    public function update(): void
    {
        $result = fgets($this->socket);
        if ($result === false) {
            return;
        }
        //var_dump("Worker received: " . $result);
        $message = unserialize($result);
        if ($message instanceof RequestInterface) {
            $response = $this->handleRequest($message);
            fwrite($this->socket, serialize($response) . PHP_EOL);
            return;
        }
        if ($message instanceof ResponseInterface) {
            $this->promises[$message->getRequestId()]->resolve($message);
            $this->status = WorkerStatus::IDLE;
        }
    }

    public function stop(): void
    {
        posix_kill($this->pid, SIGTERM);
    }
}