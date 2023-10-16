<?php

namespace Aternos\Taskmaster\Task;

use ReflectionException;
use ReflectionObject;

trait TaskMessageTrait
{
    protected array $synchronizedProperties = [];

    /**
     * @param TaskInterface $task
     * @return $this
     */
    public function loadFromTask(TaskInterface $task): static
    {
        $reflectionObject = new ReflectionObject($task);
        foreach ($reflectionObject->getProperties() as $property) {
            if ($property->isStatic() || !$property->isInitialized($task)) {
                continue;
            }
            $attributes = $property->getAttributes(Synchronized::class);
            if (count($attributes) === 0) {
                continue;
            }
            $name = $property->getName();
            $this->synchronizedProperties[$name] = $property->getValue($task);
        }
        return $this;
    }

    /**
     * @param TaskInterface $task
     * @return $this
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