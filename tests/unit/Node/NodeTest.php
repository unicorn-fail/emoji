<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Node;

use League\Emoji\Node\Document;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testContent(): void
    {
        $node1 = new Node1('foo');

        $this->assertSame('foo', $node1->getContent());
        $this->assertSame('foo', (string) $node1);
    }

    public function testClone(): void
    {
        $document = new Document();
        $node1    = new Node1();

        $document->appendNode($node1);

        $this->assertSame($document, $node1->getDocument());

        // Ensure cloned nodes aren't attached to the document.
        $clone = clone $node1;
        $this->assertNull($clone->getDocument());
        $this->assertNotFalse(\array_search($node1, $document->getNodes(), true));
        $this->assertFalse(\array_search($clone, $document->getNodes(), true));
    }

    public function testData(): void
    {
        $node1 = new Node1();

        $this->assertFalse($node1->has('foo'));
        $this->assertFalse($node1->get('foo', false));

        $node1->set('foo', 'bar');
        $this->assertTrue($node1->has('foo'));
        $this->assertSame('bar', $node1->get('foo'));
    }

    public function testAttributes(): void
    {
        $node1 = new Node1();

        $this->assertFalse($node1->hasAttribute('foo'));
        $this->assertFalse($node1->getAttribute('foo', false));

        $node1->setAttribute('foo', 'bar');
        $this->assertTrue($node1->hasAttribute('foo'));
        $this->assertSame('bar', $node1->getAttribute('foo'));
        $this->assertSame(['foo' => 'bar'], $node1->getAttributes()->export());

        $node1->setAttributes(['baz' => 'qux']);
        $node1->addClass('foo', 'bar', 'baz');
        $this->assertSame([
            'baz' => 'qux',
            'class' => 'foo bar baz',
        ], $node1->getAttributes()->export());
    }

    public function testReplaceWith(): void
    {
        $document = new Document();
        $node1    = new Node1();
        $node2    = new Node2();

        $document->appendNode($node1);

        $this->assertSame($document, $node1->getDocument());
        $this->assertNotFalse(\array_search($node1, $document->getNodes(), true));

        $node1->replaceWith($node2);

        // Ensure first node is not attached to the document.
        $this->assertNull($node1->getDocument());
        $this->assertFalse(\array_search($node1, $document->getNodes(), true));

        // Ensure second node is attached to the document.
        $this->assertSame($document, $node2->getDocument());
        $this->assertNotFalse(\array_search($node2, $document->getNodes(), true));
    }
}
