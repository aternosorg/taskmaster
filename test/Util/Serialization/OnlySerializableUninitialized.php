<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

use Aternos\Taskmaster\Communication\Serialization\Serializable;

class OnlySerializableUninitialized extends SerializationExample
{
    public int $publicNot = 1;
    #[Serializable] public int $public;
    protected int $protectedNot = 2;
    #[Serializable] protected int $protected;
    private int $privateNot = 3;
    #[Serializable] private int $private;
}