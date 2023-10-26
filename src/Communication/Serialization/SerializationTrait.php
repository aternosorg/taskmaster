<?php

namespace Aternos\Taskmaster\Communication\Serialization;

use ReflectionClass;

/**
 * Trait SerializationTrait
 *
 * This trait is used to serialize objects using the #[Serializable] and #[NotSerializable] attributes.
 *
 * You can use the #[Serializable] attribute to mark properties that should be serialized.
 * When using only the #[Serializable] attribute, all properties that are not marked with the
 * #[Serializable] attribute will be ignored.
 *
 * You can use the #[NotSerializable] attribute to mark properties that should not be serialized.
 * When using only the #[NotSerializable] attribute, all properties that are not marked with the
 * #[NotSerializable] attribute will be serialized.
 *
 * When using both attributes, all properties MUST be marked with either the #[Serializable] or #[NotSerializable]
 * attribute, otherwise an exception will be thrown.
 *
 * @package Aternos\Taskmaster\Communication\Serialization
 */
trait SerializationTrait
{
    public function __serialize(): array
    {
        $unknown = [];
        $serializable = [];
        $hasNotSerializable = false;
        $hasSerializable = false;
        $hasUnknown = false;
        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            if ($property->getAttributes(NotSerializable::class)) {
                $hasNotSerializable = true;
                continue;
            }

            if ($property->getAttributes(Serializable::class)) {
                $hasSerializable = true;
                if ($property->isInitialized($this)) {
                    $serializable[$property->getName()] = $property->getValue($this);
                }
                continue;
            }

            $hasUnknown = true;
            if ($property->isInitialized($this)) {
                $unknown[$property->getName()] = $property->getValue($this);
            }
        }

        if ($hasNotSerializable) {
            if (!$hasSerializable) {
                return $unknown;
            }
            if ($hasUnknown) {
                throw new \LogicException("Found unknown properties (" . implode(", ", array_keys($unknown)) . ") on object using both, #[Serializable] and #[NotSerializable] attributes.");
            }
            return $serializable;
        }

        if ($hasSerializable) {
            return $serializable;
        }
        return $unknown;
    }
}