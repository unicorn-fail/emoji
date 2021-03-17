<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Util;

use League\Emoji\Util\Normalize;
use League\Emoji\Util\Property;
use PHPUnit\Framework\TestCase;

class NormalizeTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function providerTypes(): array
    {
        $data['array']     = ['', 'array', ['']];
        $data['!?array']   = ['', '!?array', null];
        $data['bool']      = ['', 'bool', false];
        $data['!?bool']    = ['', '!?bool', null];
        $data['boolean']   = ['1', 'boolean', true];
        $data['!?boolean'] = ['1', '!?boolean', true];
        $data['float']     = ['', 'float', 0.0];
        $data['!?float']   = ['', '!?float', null];
        $data['double']    = ['1', 'double', 1.0];
        $data['!?double']  = ['1', '!?double', 1.0];
        $data['int']       = ['', 'int', 0];
        $data['?int']      = [0, '?int', 0];
        $data['!?int']     = [0, '!?int', null];
        $data['integer']   = ['1', 'integer', 1];
        $data['?integer']  = [null, '?integer', null];
        $data['!?integer'] = ['1', '!?integer', 1];
        $data['null']      = ['1', 'null', null];
        $data['!?null']    = ['1', '!?null', null];
        $data['string']    = [2.5, 'string', '2.5'];
        $data['!?string']  = [0, '!?string', null];

        $fooBar           = new \stdClass();
        $fooBar->foo      = 'bar';
        $data['object']   = [['foo' => 'bar'], 'object', $fooBar];
        $data['!?object'] = [[], '!?object', null];

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function providerProperties(): array
    {
        $data['!?array'] = ['!?array', '', null];
        $data['?bool']   = ['?bool', '', null];
        $data['?int']    = ['?int', 0, 0];
        $data['?float']  = ['?float', null, null];
        $data['float[]'] = ['float[]', '2.75', [2.75]];

        $data['string[]<\League\Emoji\Util\Normalize::shortcodes>'] = [
            'string[]<\League\Emoji\Util\Normalize::shortcodes>',
            'foo bar',
            ['foo-bar'],
        ];

        $data['?string[]<\League\Emoji\Util\Normalize::shortcodes>'] = [
            '?string[]<\League\Emoji\Util\Normalize::shortcodes>',
            [null],
            [],
        ];

        $data['\League\Emoji\Tests\Unit\Util\TestNormalizeIterable[]'] = [
            '\League\Emoji\Tests\Unit\Util\TestNormalizeIterable',
            'test',
            new TestNormalizeIterable('test'),
        ];

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
    public function testSetType($value, string $type, $expected): void
    {
        $actual = Property::cast($type, $value);
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
