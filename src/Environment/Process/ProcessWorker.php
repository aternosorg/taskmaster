<?php

namespace Aternos\Taskmaster\Environment\Process;

use Aternos\Taskmaster\Worker\SocketWorker;

/**
 * Class ProcessWorker
 *
 * The process worker starts the process runtime in a separate process using {@link proc_open()}.
 * No extensions are required for this worker, and it should be available in all environments except
 * those that explicitly block {@link proc_open()}.
 *
 * @see https://www.php.net/manual/en/function.proc-open.php
 * @package Aternos\Taskmaster\Environment\Process
 */
class ProcessWorker extends SocketWorker
{
    /**
     * @return bool
     */
    public static function isSupported(): bool
    {
        return function_exists("proc_open") && PHP_OS_FAMILY !== "Windows";
    }

    /**
     * @return ProcessWorkerInstance
     */
    public function createInstance(): ProcessWorkerInstance
    {
        return new ProcessWorkerInstance($this->options);
    }
}