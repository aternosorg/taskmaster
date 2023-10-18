<?php

namespace Aternos\Taskmaster\Task;

use Attribute;

/**
 * Class RunOnChild
 *
 * This attribute is used to mark a method in your {@link Task} class as a method that should only be executed on the
 * child process. Methods marked with this attribute cannot be called using the {@link Task::callAsync()} and
 * {@link Task::call()} methods. This causes a {@link RuntimeException} to be thrown.
 *
 * Note: The RunOnX attributes are mostly used as a documentation for the developer to clearly show which methods are
 * executed on which process.
 *
 * @package Aternos\Taskmaster\Task
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RunOnChild
{

}