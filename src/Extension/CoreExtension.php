<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Extension;

use UnicornFail\Emoji\Environment\EnvironmentInterface;
use UnicornFail\Emoji\Node as CoreNode;
use UnicornFail\Emoji\Renderer as CoreRenderer;

final class CoreExtension implements ExtensionInterface
{
    public function register(EnvironmentInterface $environment): void
    {
        $environment
            ->addRenderer(CoreNode\Block\Document::class, new CoreRenderer\Block\DocumentRenderer())
            ->addRenderer(CoreNode\Inline\AbstractEmoji::class, new CoreRenderer\Inline\TextRenderer())
            ->addRenderer(CoreNode\Inline\Text::class, new CoreRenderer\Inline\TextRenderer());
    }
}
