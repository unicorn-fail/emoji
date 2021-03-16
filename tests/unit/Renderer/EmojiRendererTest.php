<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Renderer\EmojiRenderer;

class EmojiRendererTest extends TestCase
{
    public function testInvalidNodeType(): void
    {
        $renderer = new EmojiRenderer();
        $node     = $this->createMock(Node::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible node type: ' . \get_class($node));

        $renderer->render($node);
    }
}
