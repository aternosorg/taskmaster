<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Worker\Instance\ProxyableSocketWorkerInstance;
use Aternos\Taskmaster\Worker\Instance\WorkerInstanceStatus;
use parallel\Channel;
use parallel\Future;
use parallel\Runtime;

/**
 * Class ThreadWorkerInstance
 *
 * NOTE: This worker instance is considered experimental and not recommended for production use, see {@link ThreadWorker}.
 *
 * The thread worker instance creates a new {@link Runtime} thread using the parallel extension.
 * When a thread worker instance dies or is stopped, the thread worker creates a new thread worker instance.
 *
 * @package Aternos\Taskmaster\Environment\Thread
 */
class ThreadWorkerInstance extends ProxyableSocketWorkerInstance
{
    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];
    protected ?Runtime $runtime = null;
    protected ?Future $future = null;

    /**
     * @inheritDoc
     */
    public function start(): static
    {
        $this->runtime = new Runtime($this->options->getBootstrap());
        $channelPair = new ChannelPair();
        $this->socket = $channelPair->getParentSocket();

        $this->future = $this->runtime->run(function (Channel $sender, Channel $receiver) {
            (new ThreadRuntime(new ChannelSocket($sender, $receiver)))->start();
        }, $channelPair->getChildSocket()->getChannels());

        $this->status = WorkerInstanceStatus::STARTING;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function stop(): static
    {
        if ($this->status !== WorkerInstanceStatus::FAILED) {
            $this->status = WorkerInstanceStatus::FINISHED;
        }
        if ($this->hasDied()) {
            return $this;
        }
        $this->runtime?->kill();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasDied(): bool
    {
        if ($this->future === null) {
            return false;
        }
        return $this->future->cancelled() || $this->future->done();
    }
}