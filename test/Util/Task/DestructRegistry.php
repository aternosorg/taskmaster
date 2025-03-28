<?php

namespace Aternos\Taskmaster\Test\Util\Task;

class DestructRegistry
{
    protected static array $objectIds = [];

    /**
     * @param object $object
     * @return void
     */
    public static function register(object $object): void
    {
        static::$objectIds[] = spl_object_id($object);
    }

    /**
     * @param object $object
     * @return void
     */
    public static function unregister(object $object): void
    {
        $objectId = spl_object_id($object);
        if (($key = array_search($objectId, static::$objectIds)) !== false) {
            unset(static::$objectIds[$key]);
        }
    }

    public static function clear(): void
    {
        static::$objectIds = [];
    }

    /**
     * @return bool
     */
    public static function empty(): bool
    {
        return empty(static::$objectIds);
    }

    /**
     * @return int
     */
    public static function count(): int
    {
        return count(static::$objectIds);
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function has(int $id): bool
    {
        return in_array($id, static::$objectIds);
    }
}