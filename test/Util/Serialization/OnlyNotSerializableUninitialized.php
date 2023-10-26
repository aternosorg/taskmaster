<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

use Aternos\Taskmaster\Communication\Serialization\NotSerializable;

class OnlyNotSerializableUninitialized extends SerializationExample
{
    #[NotSerializable] public int $publicNot;
    public int $public = 1;
    #[NotSerializable] protected int $protectedNot;
    protected int $protected = 2;
    #[NotSerializable] private int $privateNot;
    private int $private = 3;
}