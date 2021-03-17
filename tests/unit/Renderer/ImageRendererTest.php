<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Renderer;

use League\Emoji\Dataset\Emoji as DatasetEmoji;
use League\Emoji\Environment\Environment;
use League\Emoji\Node\Emoji;
use League\Emoji\Node\Image;
use League\Emoji\Node\Node;
use League\Emoji\Renderer\ImageRenderer;
use League\Emoji\Util\HtmlElement;
use PHPUnit\Framework\TestCase;

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
