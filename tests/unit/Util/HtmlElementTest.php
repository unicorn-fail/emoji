<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji\Tests\Unit\Util;

use League\Emoji\Util\HtmlElement;
use PHPUnit\Framework\TestCase;

class HtmlElementTest extends TestCase
{
    public function testConstructorOneArgument(): void
    {
        $p = new HtmlElement('p');
        $this->assertEquals('p', $p->getTagName());
        $this->assertEmpty($p->getAttributes());
        $this->assertEmpty($p->getContents());
    }

    public function testConstructorTwoArguments(): void
    {
        $img = new HtmlElement('img', ['src' => 'foo.jpg']);
        $this->assertEquals('img', $img->getTagName());
        $this->assertCount(1, $img->getAttributes());
        $this->assertEquals('foo.jpg', $img->getAttribute('src'));
        $this->assertEmpty($img->getContents());
    }

    public function testConstructorThreeArguments(): void
    {
        $li = new HtmlElement('li', ['class' => 'odd'], 'Foo');
        $this->assertEquals('li', $li->getTagName());
        $this->assertCount(1, $li->getAttributes());
        $this->assertEquals('odd', $li->getAttribute('class'));
        $this->assertEquals('Foo', $li->getContents());
    }

    public function testNonSelfClosingElement(): void
    {
        $p = new HtmlElement('p', [], '', false);

        $this->assertEquals('<p></p>', (string) $p);
    }

    public function testSelfClosingElement(): void
    {
        $hr = new HtmlElement('hr', [], '', true);

        $this->assertEquals('<hr />', (string) $hr);
    }

    public function testGetSetExistingAttribute(): void
    {
        $p = new HtmlElement('p', ['class' => 'foo']);
        $this->assertCount(1, $p->getAttributes());
        $this->assertEquals('foo', $p->getAttribute('class'));

        $p->setAttribute('class', 'bar');
        $this->assertCount(1, $p->getAttributes());
        $this->assertEquals('bar', $p->getAttribute('class'));
    }

    public function testGetSetNonExistingAttribute(): void
    {
        $p = new HtmlElement('p', ['class' => 'foo']);
        $this->assertCount(1, $p->getAttributes());
        $this->assertNull($p->getAttribute('id'));

        $p->setAttribute('id', 'bar');
        $this->assertCount(2, $p->getAttributes());
        $this->assertEquals('bar', $p->getAttribute('id'));
        $this->assertEquals('foo', $p->getAttribute('class'));
    }

    public function testGetSetAttributeWithStringAndArrayValues(): void
    {
        $p = new HtmlElement('p', ['class' => ['foo', 'bar']]);
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('foo bar', $p->getAttribute('class'));

        $p->addClass('baz');
        $this->assertSame('foo bar baz', $p->getAttribute('class'));

        $p->setAttribute('class', 'baz');
        $this->assertSame('baz', $p->getAttribute('class'));

        $p->setAttribute('class', ['foo', 'bar', 'baz']);
        $this->assertSame('foo bar baz', $p->getAttribute('class'));

        $p->setAttribute('class', 'foo bar');
        $this->assertSame('foo bar', $p->getAttribute('class'));
    }

    public function testAttributesWithArrayValues(): void
    {
        // Classes have duplicate values removed (array or string).
        $p = new HtmlElement('p', ['class' => ['a', 'b', 'a']]);
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('a b', $p->getAttribute('class'));
        $this->assertSame('<p class="a b"></p>', $p->__toString());

        $p->setAttribute('class', ['foo', 'bar__baz', 'foo']);
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('foo bar__baz', $p->getAttribute('class'));
        $this->assertSame('<p class="foo bar__baz"></p>', $p->__toString());

        $p->setAttribute('class', 'x y z x a');
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('x y z a', $p->getAttribute('class'));
        $this->assertSame('<p class="x y z a"></p>', $p->__toString());

        // Normal attribute values only have duplicates removed if an array is passed.
        $p = new HtmlElement('p', ['data-attribute' => ['a', 'b', 'a']]);
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('a b', $p->getAttribute('data-attribute'));
        $this->assertSame('<p data-attribute="a b"></p>', $p->__toString());

        $p->setAttribute('data-attribute', ['foo', 'bar', 'foo']);
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('foo bar', $p->getAttribute('data-attribute'));
        $this->assertSame('<p data-attribute="foo bar"></p>', $p->__toString());

        $p->setAttribute('data-attribute', 'x y z x a');
        $this->assertCount(1, $p->getAttributes());
        $this->assertSame('x y z x a', $p->getAttribute('data-attribute'));
        $this->assertSame('<p data-attribute="x y z x a"></p>', $p->__toString());
    }

    public function testAttributesWithBooleanTrueValues(): void
    {
        $checkbox = new HtmlElement('input', ['type' => 'checkbox', 'checked' => true], '', true);
        $this->assertSame('<input type="checkbox" checked>', $checkbox->__toString());

        $checkbox->setAttribute('checked', false);
        $this->assertSame('<input type="checkbox">', $checkbox->__toString());

        $checkbox->setAttribute('checked', true);
        $this->assertSame('<input type="checkbox" checked>', $checkbox->__toString());
    }

    public function testToString(): void
    {
        $img = new HtmlElement('img', [], '', true);
        $p   = new HtmlElement('p');
        $div = new HtmlElement('div');

        $div->setContents($p);
        $this->assertEquals('<p></p>', $div->getContents(true));

        $div->setContents([$p, $img]);
        $this->assertIsString($div->getContents(true));
        $this->assertEquals('<p></p><img />', $div->getContents(true));

        $this->assertEquals('<div><p></p><img /></div>', $div->__toString());
    }

    public function testToStringWithUnescapedAttribute(): void
    {
        $element = new HtmlElement('p', ['id' => 'foo', 'data-attribute' => 'test" onclick="javascript:doBadThings();'], 'click me');
        $this->assertEquals('<p id="foo" data-attribute="test&quot; onclick=&quot;javascript:doBadThings();">click me</p>', $element->__toString());

        // Ensure class sanitizes everything.
        $element = new HtmlElement('p', ['id' => 'foo', 'class' => 'test" onclick="javascript:doBadThings();'], 'click me');
        $this->assertEquals('<p id="foo" class="test onclickjavascriptdoBadThings">click me</p>', $element->__toString());
    }

    public function testNullContentConstructor(): void
    {
        $img = new HtmlElement('img', [], null);
        $this->assertTrue($img->getContents(false) === '');
    }

    public function testNullContentSetter(): void
    {
        $img = new HtmlElement('img');
        $img->setContents(null);
        $this->assertTrue($img->getContents(false) === '');
    }

    /**
     * See https://github.com/thephpleague/commonmark/issues/376
     */
    public function testRegressionWith0NotBeingRendered(): void
    {
        $element = new HtmlElement('em');
        $element->setContents('0');
        $this->assertSame('0', $element->getContents());

        $element = new HtmlElement('em', [], '0');
        $this->assertSame('0', $element->getContents());
    }
}
