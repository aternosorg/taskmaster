<?php

namespace Aternos\Taskmaster\Task;

use Attribute;

/**
 * Class OnParent
 *
 * This attribute can be used to mark a method or property in your {@link Task} to only be used on the parent process.
 *
 * Methods marked with this attribute can be called using the {@link Task::callAsync()} and {@link Task::call()} methods.
 *
 * Properties marked with this attribute are not serialized and send to the child process. They are therefore not available
 * in your {@link Task::run()} method. They can be unserializable, e.g. closures or resources.
 *
 * @package Aternos\Taskmaster\Task
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class OnParent
{

}