<?php

declare(strict_types=1);

namespace League\Emoji\Extension\Twemoji;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use League\Emoji\EmojiConverterInterface;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Node\Emoji;
use League\Emoji\Node\Image;
use League\Emoji\Node\Node;
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
        foreach ($e->getDocument()->getNodes() as $node) {
            // Only convert types that are set to "twemoji".
            if (! ($node instanceof Emoji) || $node->hexcode === null || $this->getConversionType($node) !== self::CONVERSION_TYPE) {
                continue;
            }

            $twemoji = $this->getTwemojiImage($node);

            $node->replaceWith($twemoji);
        }
    }

    protected function addClassesToNode(Node $node, ?string ...$classes): void
    {
        $prefix = $this->getClassPrefix();

        $classes = \array_map(static function (string $class) use ($prefix) {
            return HtmlElement::cleanCssIdentifier($prefix . $class);
        }, \array_filter($classes));

        $classes = \array_unique(\array_filter(\array_merge($this->getClasses(), $classes)));

        $node->addClass(...$classes);
    }

    /** @return string[] */
    protected function getClasses(): array
    {
        /** @var string[] $classes */
        $classes = (array) $this->config->get('twemoji.classes');

        return $classes;
    }

    protected function getClassPrefix(): string
    {
        return (string) $this->config->get('twemoji.classPrefix');
    }

    protected function getConversionType(Emoji $emoji): ?string
    {
        $parsedType     = $emoji->getParsedType();
        $configPath     = 'convert.' . (EmojiConverterInterface::TYPES[$parsedType] ?? '');
        $conversionType = null;
        if ($this->config->exists($configPath)) {
            $conversionType = (string) ($this->config->get($configPath) ?? '');
        }

        return $conversionType;
    }

    protected function getImageType(): string
    {
        return (string) $this->config->get('twemoji.type');
    }

    /** @return int|float|string|null */
    protected function getSize()
    {
        /** @var int|float|string|null $size */
        $size = $this->config->get('twemoji.size');

        return $size;
    }

    protected function getTwemojiImage(Emoji $emoji): Image
    {
        $image = new Image($emoji->getParsedValue(), $emoji, $this->getUrl((string) $emoji->hexcode), $emoji->annotation, $emoji->annotation);

        $this->addClassesToNode($image, $emoji->annotation);

        // Ensure image isn't massive and relative to its surroundings by inlining it.
        $size = $this->getSize();
        if ($this->isInline()) {
            if ($size === null) {
                $size = '1em';
            } elseif (! \is_string($size)) {
                $size .= 'em';
            }

            $image->setAttribute('style', \sprintf('width: %s; height: %s; vertical-align: middle;', $size, $size));
        } elseif ($size !== null) {
            $image->setAttribute('height', (string) $size);
            $image->setAttribute('width', (string) $size);
        }

        return $image;
    }

    protected function getUrl(string $hexcode): string
    {
        return \sprintf(
            '%s/%s/%s.%s',
            $this->getUrlBase(),
            ($imageType = $this->getImageType()) === 'png' ? '72x72' : 'svg',
            \strtolower($hexcode),
            $imageType
        );
    }

    protected function getUrlBase(): string
    {
        return (string) $this->config->get('twemoji.urlBase');
    }

    protected function isInline(): bool
    {
        return (bool) $this->config->get('twemoji.inline');
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}
