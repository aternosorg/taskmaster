<?php

namespace Aternos\Taskmaster\Test\Unit\Communication\Serialization;

use Aternos\Taskmaster\Test\Util\Serialization\BothAttributes;
use Aternos\Taskmaster\Test\Util\Serialization\BothAttributesAndUnknown;
use Aternos\Taskmaster\Test\Util\Serialization\BothAttributesAndUnknownUninitialized;
use Aternos\Taskmaster\Test\Util\Serialization\BothAttributesUninitialized;
use Aternos\Taskmaster\Test\Util\Serialization\OnlyNotSerializable;
use Aternos\Taskmaster\Test\Util\Serialization\OnlyNotSerializableUninitialized;
use Aternos\Taskmaster\Test\Util\Serialization\OnlySerializable;
use Aternos\Taskmaster\Test\Util\Serialization\OnlySerializableUninitialized;
use Aternos\Taskmaster\Test\Util\Serialization\WithoutAttributes;
use Aternos\Taskmaster\Test\Util\Serialization\WithoutAttributesUninitialized;
use PHPUnit\Framework\TestCase;

class SerializationTest extends TestCase
{
    public function testWithoutAttributes(): void
    {
        $object = new WithoutAttributes();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testWithoutAttributesUninitialized(): void
    {
        $object = new WithoutAttributesUninitialized();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testOnlySerializable(): void
    {
        $object = new OnlySerializable();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testOnlySerializableUninitialized(): void
    {
        $object = new OnlySerializableUninitialized();
        $this->assertEquals([], $object->__serialize());
    }

    public function testOnlyNotSerializable(): void
    {
        $object = new OnlyNotSerializable();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testOnlyNotSerializableUninitialized(): void
    {
        $object = new OnlyNotSerializableUninitialized();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testBothAttributes(): void
    {
        $object = new BothAttributes();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testBothAttributesUninitialized(): void
    {
        $object = new BothAttributesUninitialized();
        $this->assertEquals([
            "public" => 1,
            "protected" => 2,
            "private" => 3
        ], $object->__serialize());
    }

    public function testBothAttributesAndUnknown(): void
    {
        $object = new BothAttributesAndUnknown();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Found unknown properties (unknown) on object using both, #[Serializable] and #[NotSerializable] attributes.");
        $object->__serialize();
    }

    public function testBothAttributesAndUnknownUninitialized(): void
    {
        $object = new BothAttributesAndUnknownUninitialized();
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Found unknown properties (unknown) on object using both, #[Serializable] and #[NotSerializable] attributes.");
        $object->__serialize();
    }
}