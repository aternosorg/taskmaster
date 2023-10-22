<?php

namespace Aternos\Taskmaster\Task;

use Attribute;

/**
 * Class OnBoth
 *
 * This attribute can be used to mark a method or property in your {@link Task} to be used on both the child and the
 * parent process. This is the default, if no other attribute is used.
 *
 * Methods marked with this attribute can be called using the {@link Task::callAsync()} and {@link Task::call()} methods.
 *
 * Properties marked with this attribute are serialized and send to the child process. They are available in your
 * {@link Task::run()} method and are send back to the parent process during {@link Task::callAsync()} and
 * {@link Task::call()} calls and when the task is finished.
 * They must be serializable at all times.
 *
 * @package Aternos\Taskmaster\Task
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class OnBoth
{

}