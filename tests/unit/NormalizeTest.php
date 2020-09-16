<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Normalize;

class NormalizeTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function providerTypes(): array
    {
        $data['array']     = ['', 'array', false, ['']];
        $data['!?array']   = ['', 'array', true, null];
        $data['bool']      = ['', 'bool', false, false];
        $data['!?bool']    = ['', 'bool', true, null];
        $data['boolean']   = ['1', 'boolean', false, true];
        $data['!?boolean'] = ['1', 'boolean', true, true];
        $data['float']     = ['', 'float', false, 0.0];
        $data['!?float']   = ['', 'float', true, null];
        $data['double']    = ['1', 'double', false, 1.0];
        $data['!?double']  = ['1', 'double', true, 1.0];
        $data['int']       = ['', 'int', false, 0];
        $data['!?int']     = ['', 'int', true, null];
        $data['integer']   = ['1', 'integer', false, 1];
        $data['!?integer'] = ['1', 'integer', true, 1];
        $data['null']      = ['1', 'null', false, null];
        $data['!?null']    = ['1', 'null', true, null];
        $data['string']    = [2.5, 'string', false, '2.5'];
        $data['!?string']  = [0, 'string', true, null];

        $fooBar           = new \stdClass();
        $fooBar->foo      = 'bar';
        $data['object']   = [['foo' => 'bar'], 'object', false, $fooBar];
        $data['!?object'] = [[], 'object', true, null];

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function providerProperties(): array
    {
        $data['!?array'] = ['!?array', '', null];
        $data['?bool']   = ['?bool', '', null];
        $data['float[]'] = ['float[]', '2.75', [2.75]];

        $data['\UnicornFail\Emoji\Normalize::shortcodes[]'] = ['\UnicornFail\Emoji\Normalize::shortcodes[]', 'foo bar', ['foo-bar']];

        $data['\UnicornFail\Emoji\Tests\Unit\TestNormalizeIterable[]'] = ['\UnicornFail\Emoji\Tests\Unit\TestNormalizeIterable[]', 'test', new TestNormalizeIterable('test')];

        return $data;
    }

    /**
     * @dataProvider providerProperties
     *
     * @param mixed $raw
     * @param mixed $expected
     */
    public function testProperties(string $type, $raw, $expected): void
    {
        $actual = Normalize::properties([$type => $raw], [$type => $type]);
        $this->assertEquals([$type => $expected], $actual);
    }

    /**
     * @dataProvider providerTypes
     *
     * @param mixed $value
     * @param mixed $expected
     */
    public function testSetType($value, string $type, bool $emptyNullable, $expected): void
    {
        $actual = Normalize::setType($value, $type, $emptyNullable);
        $this->assertEquals($expected, $actual);
    }
}

// phpcs:disable

class TestNormalizeIterable extends \ArrayObject
{
    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct((array) $input, $flags, $iterator_class);
    }

}
