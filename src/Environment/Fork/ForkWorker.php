<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace Aternos\Taskmaster\Environment\Fork;

use Aternos\Taskmaster\Worker\SocketWorker;

class ForkWorker extends SocketWorker
{

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
        $this->readSocket = $this->writeSocket = $sockets[0];
        $this->pid = $pid;
        stream_set_blocking($this->readSocket, false);
    }

    public function stop(): void
    {
        posix_kill($this->pid, SIGTERM);
    }
}