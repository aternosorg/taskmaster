<?php

namespace Aternos\Taskmaster\Test\Util\Serialization;

class WithoutAttributes extends SerializationExample
{
    public int $public = 1;
    protected int $protected = 2;
    private int $private = 3;
}