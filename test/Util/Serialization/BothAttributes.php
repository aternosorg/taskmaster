<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

use Aternos\Taskmaster\Communication\Serialization\NotSerializable;
use Aternos\Taskmaster\Communication\Serialization\Serializable;

class BothAttributes extends SerializationExample
{
    #[NotSerializable] public int $publicNot = 1;
    #[Serializable] public int $public = 1;
    #[NotSerializable] protected int $protectedNot = 2;
    #[Serializable] protected int $protected = 2;
    #[NotSerializable] private int $privateNot = 3;
    #[Serializable] private int $private = 3;
}