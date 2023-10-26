<?php

namespace Aternos\Taskmaster\Communication\Serialization;

use Attribute;

/**
 * Attribute NotSerializable
 *
 * This attribute is used to mark properties that should not be serialized.
 * When using only the #[NotSerializable] attribute, all properties that are not marked with the
 * #[NotSerializable] attribute will be serialized.
 *
 * When using both attributes, all properties MUST be marked with either the #[Serializable] or #[NotSerializable].
 *
 * You can use the {@link SerializationTrait} to implement serialization with these attributes.
 *
 * @package Aternos\Taskmaster\Communication\Serialization
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class NotSerializable
{

}