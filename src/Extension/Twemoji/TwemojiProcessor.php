<?php

declare(strict_types=1);

namespace League\Emoji\Extension\Twemoji;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use League\Emoji\EmojiConverterInterface;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Node\Emoji;
use League\Emoji\Node\Image;
use League\Emoji\Util\HtmlElement;

/**
 * Replaces emojis with Twemoji images.
 */
final class TwemojiProcessor implements ConfigurationAwareInterface
{
    public const CONVERSION_TYPE = 'twemoji';

    /**
     * @var ConfigurationInterface
     *
     * @psalm-readonly-allow-private-mutation
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $config;

    public function __invoke(DocumentParsedEvent $e): void
    {
        $urlBase = (string) $this->config->get('twemoji.urlBase');

        $classPrefix = (string) $this->config->get('twemoji.classPrefix');

        /** @var int|float|string|null $size */
        $size = $this->config->get('twemoji.size');

        $inline = (bool) $this->config->get('twemoji.inline');

        $type = (string) $this->config->get('twemoji.type');

        foreach ($e->getDocument()->getNodes() as $node) {
            if (! ($node instanceof Emoji) || $node->hexcode === null) {
                continue;
            }

            $parsedType     = $node->getParsedType();
            $configPath     = 'convert.' . (EmojiConverterInterface::TYPES[$parsedType] ?? '');
            $conversionType = null;

            if ($this->config->exists($configPath)) {
                $conversionType = (string) ($this->config->get($configPath) ?? '');
            }

            // Only convert types that are set to "twemoji".
            if ($conversionType !== self::CONVERSION_TYPE) {
                continue;
            }

            $url = \sprintf(
                '%s/%s/%s.%s',
                $urlBase,
                $type === 'png' ? '72x72' : 'svg',
                \strtolower($node->hexcode),
                $type
            );

            $image = new Image($node->getParsedValue(), $node, $url, $node->annotation, $node->annotation);

            /** @var string[] $classes */
            $classes = (array) $this->config->get('twemoji.classes');
            $image->addClass(...$classes);

            if ($node->annotation !== null) {
                $image->addClass(HtmlElement::cleanCssIdentifier($classPrefix
                    ? $classPrefix . $node->annotation
                    : $node->annotation));
            }

            // Ensure image isn't massive and relative to its surroundings by inlining it.
            if ($inline && $size === null) {
                $image->setAttribute('style', 'width: 1em; height: 1em; vertical-align: middle;');
            } elseif ($inline && $size !== null) {
                if (! \is_string($size)) {
                    $size .= 'em';
                }

                $image->setAttribute('style', \sprintf('width: %s; height: %s; vertical-align: middle;', $size, $size));
            } elseif ($size !== null) {
                $image->setAttribute('height', (string) $size);
                $image->setAttribute('width', (string) $size);
            }

            $node->replaceWith($image);
        }
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}
