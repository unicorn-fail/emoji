<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Token\AbstractToken;
use UnicornFail\Emoji\Token\Emoticon;
use UnicornFail\Emoji\Token\HtmlEntity;
use UnicornFail\Emoji\Token\Shortcode;
use UnicornFail\Emoji\Token\Text;
use UnicornFail\Emoji\Token\Unicode;
use UnicornFail\Emoji\Util\Normalize;

class Parser implements ParserInterface
{
    private const TYPE_METHODS = [
        Lexer::T_TEXT => 'parseText',
        Lexer::T_EMOTICON => 'parseEmoticon',
        Lexer::T_HTML_ENTITY => 'parseHtmlEntity',
        Lexer::T_SHORTCODE => 'parseShortcode',
        Lexer::T_UNICODE => 'parseUnicode',
    ];

    /** @var ConfigurationInterface */
    private $configuration;

    /** @var Dataset */
    private $dataset;

    /** @var Lexer */
    private $lexer;

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public function __construct(?iterable $configuration = null, ?Dataset $dataset = null, ?Lexer $lexer = null)
    {
        $this->configuration = Configuration::create($configuration);
        $locale              = $this->configuration->get('locale');
        $preset              = $this->configuration->get('preset');
        $this->dataset       = $dataset ?? self::loadLocalePreset($locale, $preset);
        $this->lexer         = $lexer ?? new Lexer($this->configuration);
    }

    /**
     * @param string[] $presets
     */
    protected static function loadLocalePreset(string $locale = 'en', array $presets = ShortcodeInterface::DEFAULT_PRESETS): Dataset
    {
        $throwables = [];
        $presets    = \array_filter($presets);
        $remaining  = $presets;
        while (\count($remaining) > 0) {
            $preset = \array_shift($remaining);
            try {
                return Dataset::unarchive(\sprintf('%s/%s/%s.gz', Dataset::DIRECTORY, $locale, $preset));
            } catch (\Throwable $throwable) {
                $throwables[$preset] = $throwable;
            }
        }

        throw new LocalePresetException($locale, $throwables);
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @return AbstractToken[]
     */
    public function parse(string $input): array
    {
        $this->lexer->setInput($input);
        $this->lexer->moveNext();

        return $this->parseTokens();
    }

    /**
     * @return AbstractToken[]
     */
    protected function parseTokens(): array
    {
        $tokens = [];
        while (true) {
            if (! $this->lexer->lookahead) {
                break;
            }

            $this->lexer->moveNext();

            $currentToken = \array_merge([
                'type' => Lexer::T_TEXT,
                'value' => '',
            ], $this->lexer->token ?? []);

            $method = self::TYPE_METHODS[$currentToken['type']] ?? null;
            if ($method && ($token = $this->$method($currentToken['value'])) instanceof AbstractToken) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    protected function parseEmoticon(string $value): ?Emoticon
    {
        $emoji = $this->dataset->indexBy('emoticon')->offsetGet($value);

        // Clone the configuration here. This is necessary so it can be passed to tokens,
        // which may be rendered at a later time; when the configuration may have changed.
        return $emoji ? new Emoticon(clone $this->configuration, $value, $emoji) : null;
    }

    protected function parseHtmlEntity(string $value): ?HtmlEntity
    {
        $emoji = $this->dataset->indexBy('htmlEntity')->offsetGet($value);

        // Clone the configuration here. This is necessary so it can be passed to tokens,
        // which may be rendered at a later time; when the configuration may have changed.
        $configuration = clone $this->configuration;

        return $emoji ? new HtmlEntity($configuration, $value, $emoji) : null;
    }

    protected function parseShortcode(string $value): ?Shortcode
    {
        $emoji = $this->dataset->indexBy('shortcodes')->offsetGet(\current(Normalize::shortcodes($value)));

        // Clone the configuration here. This is necessary so it can be passed to tokens,
        // which may be rendered at a later time; when the configuration may have changed.
        return $emoji ? new Shortcode(clone $this->configuration, $value, $emoji) : null;
    }

    protected function parseUnicode(string $value): ?Unicode
    {
        $emoji = $this->dataset->indexBy('emoji')->offsetGet($value) ?: $this->dataset->indexBy('text')->offsetGet($value);

        // Clone the configuration here. This is necessary so it can be passed to tokens,
        // which may be rendered at a later time; when the configuration may have changed.
        return $emoji ? new Unicode(clone $this->configuration, $value, $emoji) : null;
    }

    protected function parseText(string $value): ?Text
    {
        $text = '';
        while (true) {
            $text .= $value;
            if ($this->lexer->lookahead === null || $this->lexer->lookahead['type'] !== Lexer::T_TEXT) {
                break;
            }

            $value = $this->lexer->lookahead['value'] ?? '';
            $this->lexer->moveNext();
        }

        return $text ? new Text($text) : null;
    }
}
