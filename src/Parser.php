<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use Doctrine\Common\Lexer\AbstractLexer;
use UnicornFail\Emoji\Token\AbstractToken;
use UnicornFail\Emoji\Token\Emoticon;
use UnicornFail\Emoji\Token\HtmlEntity;
use UnicornFail\Emoji\Token\Shortcode;
use UnicornFail\Emoji\Token\Text;
use UnicornFail\Emoji\Token\Unicode;

class Parser extends AbstractLexer implements ParserInterface
{
    /** @var Configuration|ConfigurationInterface */
    private $configuration;

    /** @var Dataset */
    private $dataset;

    public function __construct(ConfigurationInterface $configuration, Dataset $dataset)
    {
        $this->configuration = $configuration;
        $this->dataset       = $dataset;
    }

    /**
     * @return AbstractToken[]
     */
    public function parse(string $input): array
    {
        $tokens = [];

        $this->setInput($input);
        $this->moveNext();

        // Clone the configuration here. This is necessary so it can be passed to tokens,
        // which may be rendered at a later time; when the configuration may have changed.
        $configuration = clone $this->configuration;

        // Organize collection by the various indices.
        $emojis       = $this->dataset->indexBy('emoji');
        $emoticons    = $this->dataset->indexBy('emoticon');
        $htmlEntities = $this->dataset->indexBy('htmlEntity');
        $shortcodes   = $this->dataset->indexBy('shortcodes');
        $texts        = $this->dataset->indexBy('text');

        while (true) {
            if (! $this->lookahead) {
                break;
            }

            $this->moveNext();

            $token = &$this->token;
            $type  = $token['type'] ?? self::T_TEXT;
            $value = $token['value'] ?? '';

            // Extract the emoji from the value matched. Even if its converting from one token type to the same
            // token type, let it continue. This can ensure the correct values are used (i.e. user provided lower
            // case hexcodes or an aliased shortcode).
            switch ($type) {
                case self::T_TEXT:
                    $text = '';
                    while (true) {
                        $text .= $value;
                        if ($this->lookahead === null || $this->lookahead['type'] !== self::T_TEXT) {
                            break;
                        }

                        $value = $this->lookahead['value'] ?? '';
                        $this->moveNext();
                    }

                    if ($text) {
                        $tokens[] = new Text($text);
                    }

                    break;

                case self::T_EMOTICON:
                    if ($emoji = $emoticons->offsetGet($value)) {
                        $tokens[] = new Emoticon($configuration, $value, $emoji);
                    }

                    break;

                case self::T_HTML_ENTITY:
                    if ($emoji = $htmlEntities->offsetGet($value)) {
                        $tokens[] = new HtmlEntity($configuration, $value, $emoji);
                    }

                    break;

                case self::T_SHORTCODE:
                    if (
                        ($shortcode = \current(Emoji::normalizeShortcodes($value))) &&
                        ($emoji = $shortcodes->offsetGet($shortcode))
                    ) {
                        $tokens[] = new Shortcode($configuration, $value, $emoji);
                    }

                    break;

                case self::T_UNICODE:
                    if ($emoji = $emojis->offsetGet($value) ?: $texts->offsetGet($value)) {
                        $tokens[] = new Unicode($configuration, $value, $emoji);
                    }

                    break;
            }
        }

        return $tokens;
    }

    /**
     * {@inheritDoc}
     *
     * @return string[]
     */
    protected function getCatchablePatterns()
    {
        $patterns = [
            self::CODEPOINT_EMOJI_LOOSE_REGEX,
            self::HTML_ENTITY_REGEX,
            $this->configuration->get('native') ? self::SHORTCODE_NATIVE_REGEX : self::SHORTCODE_REGEX,
        ];
        if ($this->configuration->get('convertEmoticons')) {
            $patterns[] = self::EMOTICON_REGEX;
        }

        // Some regex patterns from the constants include the delimiter and modifiers. Because the
        // lexer joins these expressions together as an OR group (|), they must be removed.
        foreach ($patterns as &$pattern) {
            $pattern = \trim(\rtrim($pattern, 'imsxeADSUXJu'), '/');
        }

        return $patterns;
    }

    /**
     * {@inheritDoc}
     *
     * @return string[]
     */
    protected function getNonCatchablePatterns()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @return int
     *
     * @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection
     */
    protected function getType(&$value)
    {
        if (\preg_match(self::HTML_ENTITY_REGEX, $value)) {
            return self::T_HTML_ENTITY;
        }

        // @phpstan-ignore-next-line
        if (\preg_match($this->configuration->get('native') ? self::SHORTCODE_NATIVE_REGEX : self::SHORTCODE_REGEX, $value)) {
            return self::T_SHORTCODE;
        }

        if (\preg_match(self::EMOTICON_REGEX, $value)) {
            return self::T_EMOTICON;
        }

        if (\preg_match(self::CODEPOINT_EMOJI_LOOSE_REGEX, $value)) {
            return self::T_UNICODE;
        }

        return self::T_TEXT;
    }
}
