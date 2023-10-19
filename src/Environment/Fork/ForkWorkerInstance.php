<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Communication\Socket\SocketPair;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\Instance\ProxyableSocketWorkerInstance;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceStatus;
use RuntimeException;

/**
 * Class ForkWorkerInstance
 *
 * The fork worker instance forks the php process using the pcntl extension.
 * When a fork worker instance dies or is stopped, the fork worker creates
 * a new fork worker instance.
 *
 * @package Aternos\Taskmaster\Environment\Fork
 */
class ForkWorkerInstance extends ProxyableSocketWorkerInstance
{
    protected ?int $pid = null;

    /**
     * @inheritDoc
     */
    public function stop(): static
    {
        if ($this->status !== WorkerInstanceStatus::FAILED) {
            $this->status = WorkerInstanceStatus::FINISHED;
        }
        $this->socket?->close();
        if ($this->pid) {
            posix_kill($this->pid, SIGTERM);
            while (!$this->hasDied()) {
                usleep(Taskmaster::SOCKET_WAIT_TIME);
            }
            $this->pid = null;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function start(): static
    {
        $socketPair = new SocketPair();
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException("Could not fork");
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
     * @inheritDoc
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