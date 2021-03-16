<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use League\Emoji\Util\ImmutableArrayIterator;

class ImmutableArrayIteratorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function providerImmutabilityMethods(): array
    {
        $methods = [['__set'], ['__unset'], ['append'], ['offsetSet'], ['offsetUnset'], ['setFlags']];

        return (array) \array_combine(\array_map('current', $methods), $methods);
    }

    public function testGet(): void
    {
        $array = new TestImmutableArrayIterator(
            [
                0     => null,
                'foo' => 'bar',
                'bar' => null,
            ]
        );
        $this->assertSame('bar', $array['foo']);
        $this->assertSame('foo-bar', $array->method);
        $this->assertTrue(isset($array->method));

        $this->expectExceptionObject(new \OutOfRangeException('Unknown property: foo'));
        $array = new TestImmutableArrayIterator();
        $this->assertFalse(isset($array['foo']));
    }

    public function testUnknownArrayProperty(): void
    {
        $this->expectExceptionObject(new \OutOfRangeException('Unknown property: bar'));
        $array = new TestImmutableArrayIterator();
        $this->assertNull($array['bar']);
    }

    public function testUnknownObjectProperty(): void
    {
        $this->expectExceptionObject(new \OutOfRangeException('Unknown property: baz'));
        $array = new TestImmutableArrayIterator();
        $this->assertNull($array->baz);
    }

    /**
     * @dataProvider providerImmutabilityMethods
     */
    public function testImmutability(string $method): void
    {
        $this->expectExceptionObject(new \BadMethodCallException('Unable to modify immutable object.'));
        $array = new ImmutableArrayIterator();
        $array->$method('foo', 'bar');
    }
}

// phpcs:disable

class TestImmutableArrayIterator extends ImmutableArrayIterator
{
    public function getMethod(): string
    {
        return 'foo-bar';
    }
}
