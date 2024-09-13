<?php

namespace Aternos\Taskmaster\Runtime;

use Aternos\Taskmaster\Communication\Socket\SocketInterface;
use Aternos\Taskmaster\Communication\Socket\SocketPair;
use Aternos\Taskmaster\Communication\StdStreams;
use Aternos\Taskmaster\TaskmasterOptions;

/**
 * Class RuntimeProcess
 *
 * Manages a process that executes an {@link AsyncRuntimeInterface}.
 * Uses {@link proc_open()} to start the process.
 *
 * @package Aternos\Taskmaster\Runtime
 */
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
        $stdStreams = StdStreams::getInstance();
        $this->process = proc_open([
            $options->getPhpExecutable(),
            static::BIN_PATH,
            $options->getBootstrap(),
            $runtimeClass
        ], [
            0 => $stdStreams->getStdin(),
            1 => $stdStreams->getStdout(),
            2 => $stdStreams->getStderr(),
            3 => $socketPair->getChildSocket()->getStream(),
        ], $pipes);
        $socketPair->closeChildSocket();
    }

    /**
     * Stop the runtime process
     *
     * @return bool
     */
    public function stop(): bool
    {
        $this->socket->close();
        $result = proc_terminate($this->process);
        proc_close($this->process);
        $this->process = null;
        return $result;
    }

    /**
     * Get the socket to communicate with the runtime process
     *
     * @return SocketInterface
     */
    public function getSocket(): SocketInterface
    {
        return $this->socket;
    }

    /**
     * Check if the runtime process is currently running
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        if (!$this->process) {
            return false;
        }
        return proc_get_status($this->process)["running"];
    }
}
