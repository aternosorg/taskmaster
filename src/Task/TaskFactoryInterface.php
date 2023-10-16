<?php

namespace Aternos\Taskmaster\Task;

interface TaskFactoryInterface
{
    /**
     * @return string[]|null[]|null
     */
    public function getGroups(): ?array;

    public function createNextTask(?string $group): ?TaskInterface;
}