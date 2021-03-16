<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use League\Emoji\Node\Node;
use League\Emoji\Renderer\TextRenderer;

class TextRendererTest extends TestCase
{
    public function testInvalidNodeType(): void
    {
        $renderer = new TextRenderer();
        $node     = $this->createMock(Node::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible node type: ' . \get_class($node));

        $renderer->render($node);
    }
}
