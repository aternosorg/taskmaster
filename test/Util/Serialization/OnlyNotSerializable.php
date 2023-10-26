<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

use Aternos\Taskmaster\Communication\Serialization\NotSerializable;

class OnlyNotSerializable extends SerializationExample
{
    #[NotSerializable] public int $publicNot = 1;
    public int $public = 1;
    #[NotSerializable] protected int $protectedNot = 2;
    protected int $protected = 2;
    #[NotSerializable] private int $privateNot = 3;
    private int $private = 3;
}