<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

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

    /** @var Configuration|ConfigurationInterface */
    private $configuration;

    /** @var Dataset */
    private $dataset;

    /** @var Lexer */
    private $lexer;

    public function __construct(ConfigurationInterface $configuration, Dataset $dataset, ?Lexer $lexer = null)
    {
        $this->configuration = $configuration;
        $this->dataset       = $dataset;
        $this->lexer         = $lexer ?? new Lexer($configuration);
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
