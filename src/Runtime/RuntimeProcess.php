<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Communication\Socket\SocketPair;
use Aternos\Taskmaster\TaskmasterOptions;

class RuntimeProcess
{
    protected const BIN_PATH = __DIR__ . "/../../bin/runtime.php";

    /**
     * @var resource
     */
    protected mixed $process;

    protected SocketInterface $socket;

    /**
     * @param TaskmasterOptions $options
     * @param class-string<AsyncRuntimeInterface> $runtimeClass
     */
    public function __construct(TaskmasterOptions $options, string $runtimeClass)
    {
        $socketPair = new SocketPair();
        $this->socket = $socketPair->getParentSocket();
        $this->process = proc_open([
            "php",
            static::BIN_PATH,
            $options->getBootstrap(),
            $runtimeClass
        ], [
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
            3 => $socketPair->getChildSocket()->getStream(),
        ], $pipes);
        $socketPair->closeChildSocket();
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        $this->socket->close();
        return proc_terminate($this->process);
    }

    /**
     * @return SocketInterface
     */
    public function getSocket(): SocketInterface
    {
        return $this->socket;
    }
}