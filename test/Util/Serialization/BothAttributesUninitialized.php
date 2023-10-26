<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

use Aternos\Taskmaster\Communication\Serialization\NotSerializable;
use Aternos\Taskmaster\Communication\Serialization\Serializable;

class BothAttributesUninitialized extends SerializationExample
{
    #[NotSerializable] public int $publicNot;
    #[Serializable] public int $public = 1;
    #[NotSerializable] protected int $protectedNot;
    #[Serializable] protected int $protected = 2;
    #[NotSerializable] private int $privateNot;
    #[Serializable] private int $private = 3;
}