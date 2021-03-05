<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Extension;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use UnicornFail\Emoji\EmojiConverterInterface;
use UnicornFail\Emoji\Emojibase\EmojibaseDatasetInterface;
use UnicornFail\Emoji\Event\DocumentParsedEvent;
use UnicornFail\Emoji\Node\Emoji;
use UnicornFail\Emoji\Parser\Lexer;

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
        /** @var string[] $excludedShortcodes */
        $excludedShortcodes = $this->config->get('exclude/shortcodes');

        /** @var ?int $presentation */
        $presentation = $this->config->get('presentation');

        // Ensure emojis are set to the correct stringable type.
        $walker = $e->getDocument()->walker();
        while ($event = $walker->next()) {
            if (! $event->isEntering()) {
                continue;
            }

            $node = $event->getNode();
            if (! ($node instanceof Emoji)) {
                continue;
            }

            $literal    = null;
            $type       = $node->getParsedType();
            $configPath = 'convert.' . (EmojiConverterInterface::TYPES[$type] ?? '');

            /** @var ?int $conversionType */
            $conversionType = null;

            if ($this->config->exists($configPath) && ($configType = (string) ($this->config->get($configPath) ?? ''))) {
                $index = \array_search($configType, EmojiConverterInterface::TYPES, true);
                if (\is_int($index)) {
                    $conversionType = $index;
                }
            }

            // If the conversion type isn't one of the core Lexer:TYPES, then do nothing.
            // It should be handled by a different extension/processor.
            if ($conversionType === null) {
                continue;
            }

            switch ($conversionType) {
                case Lexer::T_EMOTICON:
                    $literal = $node->emoticon;
                    break;

                case Lexer::T_HTML_ENTITY:
                    $literal = $node->htmlEntity;
                    break;

                case Lexer::T_SHORTCODE:
                    if ($shortcode = $node->getShortcode($excludedShortcodes, true)) {
                        $literal = $shortcode;
                    }

                    break;

                case Lexer::T_TEXT:
                case Lexer::T_UNICODE:
                    if (($presentation ?? $node->type) === EmojibaseDatasetInterface::TEXT && $node->text) {
                        $literal = $node->text;
                    } else {
                        $literal = $node->emoji ?? $node->unicode;
                    }

                    break;
            }

            if ($literal !== null) {
                $node->setLiteral($literal);
            }
        }
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }
}