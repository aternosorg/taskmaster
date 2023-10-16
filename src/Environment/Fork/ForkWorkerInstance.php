<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Socket\SocketPair;
use Aternos\Taskmaster\Worker\Instance\ProxyableSocketWorkerInstance;
use Aternos\Taskmaster\Worker\WorkerInstanceStatus;

class ForkWorkerInstance extends ProxyableSocketWorkerInstance
{
    protected ?int $pid = null;

    public function stop(): static
    {
        $this->socket?->close();
        if ($this->pid) {
            posix_kill($this->pid, SIGTERM);
        }
        return $this;
    }

    public function start(): static
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
        $this->status = WorkerInstanceStatus::STARTING;
        return $this;
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