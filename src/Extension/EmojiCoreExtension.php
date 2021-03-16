<?php

declare(strict_types=1);

namespace League\Emoji\Extension;

use League\Emoji\Environment\EnvironmentBuilderInterface;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Node\Emoji;
use League\Emoji\Node\Image;
use League\Emoji\Node\Text;
use League\Emoji\Renderer\EmojiRenderer;
use League\Emoji\Renderer\ImageRenderer;
use League\Emoji\Renderer\TextRenderer;

final class EmojiCoreExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment
            ->addEventListener(DocumentParsedEvent::class, new EmojiCoreProcessor())
            ->addRenderer(Image::class, new ImageRenderer())
            ->addRenderer(Emoji::class, new EmojiRenderer())
            ->addRenderer(Text::class, new TextRenderer());
    }
}
