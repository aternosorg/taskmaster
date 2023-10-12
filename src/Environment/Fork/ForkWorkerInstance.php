<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Socket\SocketPair;
use Aternos\Taskmaster\Worker\SocketWorkerInstance;
use Aternos\Taskmaster\Worker\WorkerStatus;
use Throwable;

class ForkWorkerInstance extends SocketWorkerInstance
{
    protected ?int $pid = null;

    public function stop(): static
    {
        $this->socket->close();
        posix_kill($this->pid, SIGTERM);
        return $this;
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

    /**
     * @return bool
     */
    public function hasDied(): bool
    {
        if (!$this->pid) {
            return false;
        }
        $res = pcntl_waitpid($this->pid, $status, WNOHANG);
        return $res === -1 || $res > 0;
    }
}