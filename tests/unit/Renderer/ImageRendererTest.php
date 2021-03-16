<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Renderer;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Dataset\Emoji as DatasetEmoji;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Node\Emoji;
use UnicornFail\Emoji\Node\Image;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Renderer\ImageRenderer;
use UnicornFail\Emoji\Util\HtmlElement;

class ImageRendererTest extends TestCase
{
    public function testInvalidNodeType(): void
    {
        $renderer = new ImageRenderer();
        $node     = $this->createMock(Node::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incompatible node type: ' . \get_class($node));

        $renderer->render($node);
    }

    public function testAllowUnsafeLinks(): void
    {
        $config = Environment::create()->getConfiguration();

        $url = 'javascript:alert("foobar")';

        $image = new Image('foo', new Emoji(0, 'foo', new DatasetEmoji([])), $url);

        $renderer = new ImageRenderer();
        $renderer->setConfiguration($config);

        /** @var HtmlElement $img */
        $img = $renderer->render($image);

        $this->assertSame($url, (string) $img->getAttribute('src'));
    }

    public function testNotAllowUnsafeLinks(): void
    {
        $config = Environment::create([
            'allow_unsafe_links' => false,
        ])->getConfiguration();

        $url = 'javascript:alert("foobar")';

        $image = new Image('foo', new Emoji(0, 'foo', new DatasetEmoji([])), $url);

        $renderer = new ImageRenderer();
        $renderer->setConfiguration($config);

        /** @var HtmlElement $img */
        $img = $renderer->render($image);

        $this->assertSame('', (string) $img->getAttribute('src'));
    }
}
