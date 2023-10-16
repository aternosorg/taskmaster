<?php

namespace Aternos\Taskmaster\Task;

abstract class TaskFactory implements TaskFactoryInterface
{
    /**
     * @var string[]|null[]|null
     */
    protected ?array $groups = null;

    /**
     * @param array|null $groups
     */
    public function setGroups(?array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * @param string|null $group
     * @return void
     */
    public function addGroup(?string $group): void
    {
        if ($this->groups === null) {
            $this->groups = [];
        }
        $this->groups[] = $group;
    }

    /**
     * @return null[]|string[]|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }
}