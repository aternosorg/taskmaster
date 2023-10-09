<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Thread;

use Aternos\Taskmaster\Communication\Promise\Promise;
use Aternos\Taskmaster\Communication\Promise\ResponsePromise;
use Aternos\Taskmaster\Worker\SocketWorker;
use Aternos\Taskmaster\Worker\WorkerStatus;
use parallel\Channel;
use parallel\Runtime;

class ThreadWorker extends SocketWorker
{
    /**
     * @var ResponsePromise[]
     */
    protected array $promises = [];
    protected Runtime $runtime;

    public function start(): Promise
    {
        $this->runtime = new Runtime($this->options->getBootstrap());
        $channelPair = new ChannelPair();
        $this->socket = $channelPair->getParentSocket();

        $this->runtime->run(function (Channel $sender, Channel $receiver) {
            (new ThreadRuntime(new ChannelSocket($sender, $receiver)))->start();
        }, $channelPair->getChildSocket()->getChannels());

        $this->status = WorkerStatus::IDLE;
        return (new Promise())->resolve();
    }

    public function stop(): void
    {
        $this->runtime->kill();
    }
}