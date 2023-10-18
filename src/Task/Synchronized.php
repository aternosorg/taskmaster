<?php

namespace Aternos\Taskmaster\Task;

use Attribute;

/**
 * Class Synchronized
 *
 * This attribute is used to mark a property in your {@link Task} class as a property that is synchronized
 * between the parent and the child process. Properties marked with this attribute are synchronized automatically
 * when the task is executed, a remote call is made in {@link Task::callAsync()} or {@link Task::call()} or when
 * the task result is sent back to the parent.
 *
 * The synchronisation ONLY happens on those events, changes to the property are not immediately synchronized.
 * The properties marked with this attribute have to be serializable.
 *
 * @package Aternos\Taskmaster\Task
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Synchronized
{
}