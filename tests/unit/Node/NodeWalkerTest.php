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
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Node\Text;

class NodeWalkerTest extends TestCase
{
    public function testWalkEmptyNode(): void
    {
        $node   = new Text();
        $walker = $node->walker();

        $event = $walker->next();
        $this->assertSame($node, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertNull($event);

        $event = $walker->next();
        $this->assertNull($event);
    }

    public function testWalkNestedNodes(): void
    {
        $document = new Document();
        $document->appendChild($paragraph = new Text());
        $paragraph->appendChild($text2 = new Text());
        $paragraph->appendChild($text3 = new Text());
        $text3->appendChild($text4 = new Text());
        $walker = $document->walker();

        $event = $walker->next();
        $this->assertSame($document, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertSame($paragraph, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertSame($text2, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertSame($text3, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertSame($text4, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertSame($text3, $event->getNode());
        $this->assertFalse($event->isEntering());

        $event = $walker->next();
        $this->assertSame($paragraph, $event->getNode());
        $this->assertFalse($event->isEntering());

        $event = $walker->next();
        $this->assertSame($document, $event->getNode());
        $this->assertFalse($event->isEntering());

        $event = $walker->next();
        $this->assertNull($event);

        $event = $walker->next();
        $this->assertNull($event);
    }

    public function testResumeAt(): void
    {
        $document = new Document();
        $document->appendChild($text1 = new Text());
        $text1->appendChild($text2 = new Text());
        $walker = $document->walker();

        $walker->next();
        $walker->next();

        $event = $walker->next();
        $this->assertSame($text2, $event->getNode());
        $this->assertTrue($event->isEntering());

        $walker->resumeAt($text2);
        $event = $walker->next();
        $this->assertSame($text2, $event->getNode());
        $this->assertTrue($event->isEntering());

        $walker->resumeAt($text1, true);
        $event = $walker->next();
        $this->assertSame($text1, $event->getNode());
        $this->assertTrue($event->isEntering());

        $event = $walker->next();
        $this->assertSame($text2, $event->getNode());
        $this->assertTrue($event->isEntering());
    }
}
