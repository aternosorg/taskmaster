<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

use Aternos\Taskmaster\Communication\Serialization\Serializable;

class OnlySerializable extends SerializationExample
{
    public int $publicNot = 1;
    #[Serializable] public int $public = 1;
    protected int $protectedNot = 2;
    #[Serializable] protected int $protected = 2;
    private int $privateNot = 3;
    #[Serializable] private int $private = 3;
}