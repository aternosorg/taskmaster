<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

class WithoutAttributesUninitialized extends SerializationExample
{
    public int $public = 1;
    public int $publicUninitialized;
    protected int $protected = 2;
    protected int $protectedUninitialized;
    private int $private = 3;
    private int $privateUninitialized;
}