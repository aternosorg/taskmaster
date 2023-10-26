<?php

namespace Aternos\Taskmaster\Test\Performance;

use Aternos\Taskmaster\Environment\Fork\ForkWorker;
use Aternos\Taskmaster\Environment\Process\ProcessWorker;
use Aternos\Taskmaster\Environment\Sync\SyncWorker;
use Aternos\Taskmaster\Environment\Thread\ThreadWorker;
use Aternos\Taskmaster\Proxy\ProcessProxy;
use Aternos\Taskmaster\Taskmaster;
use Aternos\Taskmaster\Worker\WorkerInterface;

abstract class TaskmasterBenchCase
{
    abstract protected function setupTasks(Taskmaster $taskmaster): void;

    protected function runBench(WorkerInterface $worker, int $count = 1): void
    {
        $taskmaster = new Taskmaster();
        $taskmaster->addWorkers($worker, $count);
        $this->setupTasks($taskmaster);
        $taskmaster->wait();
        $taskmaster->stop();
    }

    public function benchSync(): void
    {
        $this->runBench(new SyncWorker());
    }

    public function bench1Fork(): void
    {
        $this->runBench(new ForkWorker());
    }

    public function bench2Forks(): void
    {
        $this->runBench(new ForkWorker(), 2);
    }

    public function bench4Forks(): void
    {
        $this->runBench(new ForkWorker(), 4);
    }

    public function bench1Process(): void
    {
        $this->runBench(new ProcessWorker());
    }

    public function bench2Processes(): void
    {
        $this->runBench(new ProcessWorker(), 2);
    }

    public function bench4Processes(): void
    {
        $this->runBench(new ProcessWorker(), 4);
    }

    public function bench1ForkProxied(): void
    {
        $this->runBench((new ForkWorker())->setProxy(new ProcessProxy()));
    }

    public function bench2ForksProxied(): void
    {
        $this->runBench((new ForkWorker())->setProxy(new ProcessProxy()), 2);
    }

    public function bench4ForksProxied(): void
    {
        $this->runBench((new ForkWorker())->setProxy(new ProcessProxy()), 4);
    }
}