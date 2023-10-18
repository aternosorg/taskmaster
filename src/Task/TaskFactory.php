<?php

namespace Aternos\Taskmaster\Task;

/**
 * Class TaskFactory
 *
 * A task factory creates tasks for the {@link Taskmaster}.
 *
 * @see TaskFactoryInterface
 * @package Aternos\Taskmaster\Task
 */
abstract class TaskFactory implements TaskFactoryInterface
{
    /**
     * @var string[]|null[]|null
     */
    protected ?array $groups = null;

    /**
     * Set the groups this task factory creates tasks for.
     *
     * Only if a task for that group is requested, the task factory will be used.
     * If null is returned, the task factory will be used for all groups.
     * If the returned array contains null, the task factory will be used for tasks without a group.
     *
     * @param array|null $groups
     */
    public function setGroups(?array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * Add a group this task factory creates tasks for.
     *
     * Only if a task for that group is requested, the task factory will be used.
     * If you add null, the task factory will be used for tasks without a group.
     *
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
     * @inheritDoc
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }
}