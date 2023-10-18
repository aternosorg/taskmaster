<?php

namespace Aternos\Taskmaster\Task;

use Attribute;

/**
 * Class RunOnBoth
 *
 * This attribute is used to mark a method in your {@link Task} class as a method that can be executed on both the
 * child and the parent process. Methods marked with this attribute can be called using the {@link Task::callAsync()}
 * and {@link Task::call()} methods.
 *
 * Note: The RunOnX attributes are mostly used as a documentation for the developer to clearly show which methods are
 * executed on which process.
 *
 * @package Aternos\Taskmaster\Task
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RunOnBoth
{

}