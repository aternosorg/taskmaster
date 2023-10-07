<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Worker\SocketWorker;

class ProcessWorker extends SocketWorker
{
    protected mixed $process = null;

    public function __construct()
    {
        parent::__construct();

        $this->process = proc_open("php " . __DIR__ . "/../../../bin/process.php", [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
            3 => ["pipe", "r"],
            4 => ["pipe", "w"]
        ], $pipes);
        $this->writeSocket = $pipes[3];
        $this->readSocket = $pipes[4];
        stream_set_blocking($this->readSocket, false);
    }

    public function stop(): void
    {
        proc_terminate($this->process);
    }
}