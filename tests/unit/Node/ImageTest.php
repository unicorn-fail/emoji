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

namespace UnicornFail\Emoji\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Dataset\Emoji as DatasetEmoji;
use UnicornFail\Emoji\Node\Emoji;
use UnicornFail\Emoji\Node\Image;
use UnicornFail\Emoji\Parser\Lexer;
use UnicornFail\Emoji\Tests\Unit\Dataset\EmojiTest;

class ImageTest extends TestCase
{
    /**
     * Tests the URL constructor parameter and getUrl() method
     */
    public function testConstructorAndGetUrl(): void
    {
        $datasetEmoji = new DatasetEmoji(EmojiTest::GRINNING_FACE);
        $value        = (string) $datasetEmoji->shortcode;
        $emoji        = new Emoji(Lexer::T_SHORTCODE, $value, $datasetEmoji);
        $url          = 'https://www.example.com/foo';

        $element = $this->getMockBuilder(Image::class)
            ->setConstructorArgs([$value, $emoji, $url])
            ->getMockForAbstractClass();
        \assert($element instanceof Image);

        $this->assertSame($emoji, $element->getEmoji());
        $this->assertEquals($url, $element->getUrl());
    }

    /**
     * Tests the setUrl() method
     */
    public function testSetUrl(): void
    {
        $datasetEmoji = new DatasetEmoji(EmojiTest::GRINNING_FACE);
        $value        = (string) $datasetEmoji->shortcode;
        $emoji        = new Emoji(Lexer::T_SHORTCODE, $value, $datasetEmoji);
        $url1         = 'https://www.example.com/foo';
        $url2         = 'https://www.example.com/bar';

        $element = $this->getMockBuilder(Image::class)
            ->setConstructorArgs([$value, $emoji, $url1])
            ->getMockForAbstractClass();
        \assert($element instanceof Image);

        $element->setUrl($url2);

        $this->assertSame($emoji, $element->getEmoji());
        $this->assertEquals($url2, $element->getUrl());
    }
}
