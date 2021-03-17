<?php

declare(strict_types=1);

namespace League\Emoji\Extension;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use League\Emoji\EmojiConverterInterface;
use League\Emoji\Emojibase\EmojibaseDatasetInterface;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Lexer\EmojiLexer;
use League\Emoji\Node\Emoji;

/**
 * Processes all parsed Emoji nodes and set the various configurations.
 */
final class EmojiCoreProcessor implements ConfigurationAwareInterface
{
    /**
     * @var ConfigurationInterface
     *
     * @psalm-readonly-allow-private-mutation
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $config;

    public function __invoke(DocumentParsedEvent $e): void
    {
        // Ensure emoji content is set to the correct conversion type.
        foreach ($e->getDocument()->getNodes() as $node) {
            if (! ($node instanceof Emoji)) {
                continue;
            }

            $content = $this->getEmojiContent($node);
            if ($content !== null) {
                $node->setContent($content);
            }
        }
    }

    protected function getConversionType(Emoji $emoji): ?int
    {
        $type       = $emoji->getParsedType();
        $configPath = 'convert.' . (EmojiConverterInterface::TYPES[$type] ?? '');

        if ($this->config->exists($configPath) && ($configType = (string) ($this->config->get($configPath) ?? ''))) {
            $index = \array_search($configType, EmojiConverterInterface::TYPES, true);
            if (\is_int($index)) {
                return $index;
            }
        }

        return null;
    }

    protected function getEmojiContent(Emoji $emoji): ?string
    {
        $content = null;

        // If the conversion type isn't one of the core Lexer:TYPES, then do nothing.
        // It should be handled by a different extension/processor.
        switch ($this->getConversionType($emoji)) {
            case EmojiLexer::T_EMOTICON:
                $content = $emoji->emoticon;
                break;

            case EmojiLexer::T_HTML_ENTITY:
                $content = $emoji->htmlEntity;
                break;

            case EmojiLexer::T_SHORTCODE:
                $content = $emoji->getShortcode((array) $this->config->get('exclude/shortcodes'), true);
                break;

            case EmojiLexer::T_TEXT:
            case EmojiLexer::T_UNICODE:
                $content = $this->getEmojiUnicode($emoji);
                break;
        }

        return $content;
    }

    protected function getEmojiUnicode(Emoji $emoji): ?string
    {
        if (($this->config->get('presentation') ?? $emoji->type) === EmojibaseDatasetInterface::TEXT && $emoji->text) {
            return $emoji->text;
        }

        return $emoji->emoji ?? $emoji->unicode;
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}
