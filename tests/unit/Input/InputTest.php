<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UnicornFail\Emoji\Tests\Unit\Input;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Exception\UnexpectedEncodingException;
use UnicornFail\Emoji\Input\Input;

final class InputTest extends TestCase
{
    public function testConstructorAndGetter(): void
    {
        $input = new Input('# Hello World!');

        $this->assertSame('# Hello World!', $input->getContent());
    }

    public function testInvalidContent(): void
    {
        $this->expectException(UnexpectedEncodingException::class);

        new Input(\chr(250));
    }

    public function testGetLines(): void
    {
        $input = new Input("# Hello World!\n\nThis is just a test.\n");

        $lines = $input->getLines();

        $this->assertSame(\iterator_to_array($lines), [
            1 => '# Hello World!',
            2 => '',
            3 => 'This is just a test.',
        ]);
    }

    public function testLineOffset(): void
    {
        $input = new Input("# Hello World!\n\nThis is just a test.\n", 3);

        $lines = $input->getLines();

        $this->assertSame(\iterator_to_array($lines), [
            4 => '# Hello World!',
            5 => '',
            6 => 'This is just a test.',
        ]);
    }

    public function testGetLineCount(): void
    {
        $input = new Input("# Hello World!\n\nThis is just a test.\n");

        $this->assertSame(3, $input->getLineCount());
    }
}
