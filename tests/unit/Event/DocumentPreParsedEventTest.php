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

namespace UnicornFail\Emoji\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Event\DocumentPreParsedEvent;
use UnicornFail\Emoji\Input\Input;
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Parser\EmojiParser;

final class DocumentPreParsedEventTest extends TestCase
{
    public function testGetDocument(): void
    {
        $document = new Document();
        $input    = new Input('');

        $event = new DocumentPreParsedEvent($document, $input);

        $this->assertSame($document, $event->getDocument());
        $this->assertSame($input, $event->getInput());
    }

    public function testReplaceInput(): void
    {
        $input = new Input('');

        $event = new DocumentPreParsedEvent(new Document(), $input);

        $this->assertSame($input, $event->getInput());

        $newInput = new Input('');
        $event->replaceInput($newInput);

        $this->assertSame($newInput, $event->getInput());
        $this->assertNotSame($input, $event->getInput());
    }

    public function testEventDispatchedAtCorrectTime(): void
    {
        $wasCalled = false;

        $environment = Environment::create();
        $environment->addEventListener(DocumentPreParsedEvent::class, static function (DocumentPreParsedEvent $event) use (&$wasCalled): void {
            $wasCalled = true;
        });

        $parser = new EmojiParser($environment);
        $parser->parse('hello world');

        $this->assertTrue($wasCalled);
    }
}
