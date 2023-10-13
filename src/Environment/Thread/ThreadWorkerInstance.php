<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Worker\ProxyableSocketWorkerInstance;
use Aternos\Taskmaster\Worker\WorkerStatus;
use parallel\Channel;
use parallel\Future;
use parallel\Runtime;

class ThreadWorkerInstance extends ProxyableSocketWorkerInstance
{
    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];
    protected Runtime $runtime;
    protected ?Future $future = null;

    public function start(): Promise
    {
        $this->runtime = new Runtime($this->options->getBootstrap());
        $channelPair = new ChannelPair();
        $this->socket = $channelPair->getParentSocket();

        $this->future = $this->runtime->run(function (Channel $sender, Channel $receiver) {
            (new ThreadRuntime(new ChannelSocket($sender, $receiver)))->start();
        }, $channelPair->getChildSocket()->getChannels());

        $this->status = WorkerStatus::STARTING;
        return (new Promise())->resolve();
    }

    public function stop(): static
    {
        if ($this->hasDied()) {
            return $this;
        }
        $this->runtime->kill();
        return $this;
    }

    public function hasDied(): bool
    {
        if ($this->future === null) {
            return false;
        }
        return $this->future->cancelled() || $this->future->done();
    }
}