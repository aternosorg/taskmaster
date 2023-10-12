<?php

namespace Aternos\Taskmaster\Worker;

use Aternos\Taskmaster\Proxy\ProxyWorker;
use Aternos\Taskmaster\Task\TaskInterface;
use Aternos\Taskmaster\Taskmaster;

abstract class Worker implements WorkerInterface
{
    protected Taskmaster $taskmaster;
    protected ?WorkerInstanceInterface $instance = null;

    /**
     * @param Taskmaster $taskmaster
     * @return $this
     */
    public function setTaskmaster(Taskmaster $taskmaster): static
    {
        $this->taskmaster = $taskmaster;
        return $this;
    }

    /**
     * @return WorkerInstanceInterface
     */
    public function getInstance(): WorkerInstanceInterface
    {
        if ($this->instance === null || $this->instance->getStatus() === WorkerStatus::FAILED) {
            $this->instance = $this->createInstance();
            if ($proxy = $this->taskmaster->getProxy()) {
                $this->instance = (new ProxyWorker($this->taskmaster->getOptions()))->setWorker($this->instance)->setProxy($proxy);
            }
            $this->instance->init()->start();
        }
        return $this->instance;
    }

    /**
     * @return $this
     */
    public function update(): static
    {
        $this->getInstance()->update();
        return $this;
    }

    /**
     * @return $this
     */
    public function stop(): static
    {
        $this->getInstance()->stop();
        return $this;
    }

    /**
     * @return WorkerStatus
     */
    public function getStatus(): WorkerStatus
    {
        return $this->getInstance()->getStatus();
    }

    /**
     * @param TaskInterface $task
     * @return $this
     */
    public function assignTask(TaskInterface $task): static
    {
        $this->getInstance()->runTask($task);
        return $this;
    }

    abstract protected function createInstance(): WorkerInstanceInterface;
}