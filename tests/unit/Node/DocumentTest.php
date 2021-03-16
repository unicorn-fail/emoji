<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Node\Node;

class DocumentTest extends TestCase
{
    public function testDocument(): void
    {
        $document = new Document();

        $node1 = new Node1();
        $node2 = new Node2();

        // Ensure nodes can be appended/prepended.
        $document->appendNode($node2);
        $document->prependNode($node1);

        $this->assertSame([$node1, $node2], $document->getNodes());

        // Ensure nodes can be replaced.
        $node3 = new Node3();
        $document->replaceNode($node2, $node3);
        $this->assertSame([$node1, $node3], $document->getNodes());

        // Ensure trying to replace a non-existent node does nothing.
        $mockNode = $this->createMock(Node::class);
        $document->replaceNode($mockNode, $node2);
        $this->assertSame([$node1, $node3], $document->getNodes());

        // Ensure nodes can be manipulated via reference.
        $nodes = &$document->getNodes();
        $nodes = [$node3, $node2, $mockNode];
        $this->assertSame([$node3, $node2, $mockNode], $document->getNodes());
    }
}
