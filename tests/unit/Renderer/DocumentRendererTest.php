<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Exception\RenderNodeException;
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Renderer\DocumentRenderer;

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
