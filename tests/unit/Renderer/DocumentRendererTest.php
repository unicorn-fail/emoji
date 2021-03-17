<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Renderer;

use League\Emoji\Environment\Environment;
use League\Emoji\Exception\RenderNodeException;
use League\Emoji\Node\Document;
use League\Emoji\Node\Node;
use League\Emoji\Renderer\DocumentRenderer;
use PHPUnit\Framework\TestCase;

class DocumentRendererTest extends TestCase
{
    public function testUnknownRenderer(): void
    {
        $renderer = new DocumentRenderer(Environment::create());
        $document = new Document();

        $node = $this->createMock(Node::class);
        $document->appendNode($node);

        $this->expectException(RenderNodeException::class);
        $this->expectExceptionMessage(\sprintf('Unable to find corresponding renderer for node type %s', \get_class($node)));

        $renderer->renderDocument($document);
    }
}
