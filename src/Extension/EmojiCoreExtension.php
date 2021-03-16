<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Extension;

use UnicornFail\Emoji\Environment\EnvironmentBuilderInterface;
use UnicornFail\Emoji\Event\DocumentParsedEvent;
use UnicornFail\Emoji\Node\Emoji;
use UnicornFail\Emoji\Node\Image;
use UnicornFail\Emoji\Node\Text;
use UnicornFail\Emoji\Renderer\EmojiRenderer;
use UnicornFail\Emoji\Renderer\ImageRenderer;
use UnicornFail\Emoji\Renderer\TextRenderer;

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
