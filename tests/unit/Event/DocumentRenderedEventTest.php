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
use League\Emoji\Event\DocumentRenderedEvent;
use League\Emoji\Node\Document;
use League\Emoji\Renderer\DocumentRenderer;
use PHPUnit\Framework\TestCase;

final class DocumentRenderedEventTest extends TestCase
{
    public function testGettersAndReplacers(): void
    {
        $content = 'foo';

        $event = new DocumentRenderedEvent($content);

        $this->assertSame($content, $event->getContent());

        // Replace the output with something else - the getter should return something different now
        $event->replaceContent('bar');

        $this->assertNotSame($content, $event->getContent());
    }

    public function testEventDispatchedAtCorrectTime(): void
    {
        $wasCalled = false;

        $environment = Environment::create();
        $environment->addEventListener(DocumentRenderedEvent::class, static function (DocumentRenderedEvent $event) use (&$wasCalled): void {
            $wasCalled = true;
            $event->replaceContent('foo');
        });

        $renderer = new DocumentRenderer($environment);
        $result   = $renderer->renderDocument(new Document());

        $this->assertTrue($wasCalled);
        $this->assertSame('foo', (string) $result);
    }
}
