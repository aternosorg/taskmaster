<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Socket\SocketPair;
use Aternos\Taskmaster\Worker\SocketWorker;
use Aternos\Taskmaster\Worker\WorkerStatus;

class ForkWorker extends SocketWorker
{
    protected int $pid;

    public function stop(): void
    {
        $this->socket->close();
        posix_kill($this->pid, SIGTERM);
    }

    public function start(): Promise
    {
        $socketPair = new SocketPair();
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \RuntimeException("Could not fork");
        }
        if ($pid === 0) {
            $socketPair->closeParentSocket();
            $runtime = new ForkRuntime($socketPair->getChildSocket());
            $runtime->start();
            exit(0);
        }
        $socketPair->closeChildSocket();
        $this->socket = $socketPair->getParentSocket();
        $this->pid = $pid;
        $this->status = WorkerStatus::IDLE;
        return (new Promise())->resolve();
    }
}