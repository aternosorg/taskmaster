<?php

namespace Aternos\Taskmaster\Task;

use Attribute;

/**
 * Class OnChild
 *
 * This attribute can be used to mark a method or property in your {@link Task} to only be used on the child process.
 *
 * Methods marked with this attribute cannot be called using the {@link Task::callAsync()} and
 * {@link Task::call()} methods. This causes a {@link RuntimeException} to be thrown.
 *
 * Properties marked with this attribute are initially serialized on the parent process and send to the child process.
 * They are available in your {@link Task::run()} method, but are not send back to the parent process during
 * {@link Task::callAsync()} and {@link Task::call()} calls or when the task is finished.
 * Therefore, you can set them to unserializable values in your {@link Task::run()}, e.g. closures or resources, but
 * they must be serializable initially, e.g. empty/uninitialized.
 *
 * @package Aternos\Taskmaster\Task
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class OnChild
{

}