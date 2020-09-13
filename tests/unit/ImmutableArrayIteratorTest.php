<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\ImmutableArrayIterator;

class ImmutableArrayIteratorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function providerImmutabilityMethods(): array
    {
        $methods = [['__set'], ['__unset'], ['append'], ['offsetSet'], ['offsetUnset'], ['setFlags']];

        return \array_combine(\array_map('current', $methods), $methods);
    }

    public function testGet(): void
    {
        $array = new TestImmutableArrayIterator([
            0 => null,
            'foo' => 'bar',
            'bar' => null,
        ]);
        $this->assertSame('bar', $array['foo']);
        $this->assertNull($array['baz']);
        $this->assertSame('foo-bar', $array->method);
        $this->assertTrue(isset($array->method));
        $this->assertNull($array->missing);
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
