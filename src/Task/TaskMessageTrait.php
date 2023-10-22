<?php

namespace Aternos\Taskmaster\Task;

use ReflectionException;
use ReflectionObject;

/**
 * Trait TaskMessageTrait
 *
 * This trait is used to implement the {@link TaskMessageInterface} interface to
 * synchronize properties between the parent and the child process.
 *
 * @package Aternos\Taskmaster\Task
 */
trait TaskMessageTrait
{
    protected array $synchronizedProperties = [];

    /**
     * @inheritDoc
     */
    public function loadFromTask(TaskInterface $task): static
    {
        $reflectionObject = new ReflectionObject($task);
        foreach ($reflectionObject->getProperties() as $property) {
            if ($property->isStatic() || !$property->isInitialized($task)) {
                continue;
            }
            if ($property->getAttributes(OnChild::class)) {
                continue;
            }
            if ($property->getAttributes(OnParent::class)) {
                continue;
            }
            $name = $property->getName();
            $this->synchronizedProperties[$name] = $property->getValue($task);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function applyToTask(TaskInterface $task): static
    {
        $reflectionObject = new ReflectionObject($task);
        foreach ($this->synchronizedProperties as $name => $value) {
            try {
                $property = $reflectionObject->getProperty($name);
            } catch (ReflectionException) {
                continue;
            }
            $property->setValue($task, $value);
        }
        return $this;
    }
}