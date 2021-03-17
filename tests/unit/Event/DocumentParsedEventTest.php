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

namespace League\Emoji\Tests\Unit\Event;

use League\Emoji\Environment\Environment;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Node\Document;
use League\Emoji\Parser\EmojiParser;
use PHPUnit\Framework\TestCase;

final class DocumentParsedEventTest extends TestCase
{
    public function testGetDocument(): void
    {
        $document = new Document();

        $event = new DocumentParsedEvent($document);

        $this->assertSame($document, $event->getDocument());
    }

    public function testEventDispatchedAtCorrectTime(): void
    {
        $wasCalled = false;

        $environment = Environment::create();
        $environment->addEventListener(DocumentParsedEvent::class, static function (DocumentParsedEvent $event) use (&$wasCalled): void {
            $wasCalled = true;
        });

        $parser = new EmojiParser($environment);
        $parser->parse('hello world');

        $this->assertTrue($wasCalled);
    }
}
