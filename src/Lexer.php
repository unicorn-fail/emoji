<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use Doctrine\Common\Lexer\AbstractLexer;
use UnicornFail\Emoji\Emojibase\RegexInterface;

class Lexer extends AbstractLexer implements RegexInterface
{
    public const T_TEXT = 0;

    public const T_EMOTICON = 1;

    public const T_HTML_ENTITY = 2;

    public const T_SHORTCODE = 3;

    public const T_UNICODE = 4;

    public const TYPES = [
        self::T_TEXT => 'text',
        self::T_EMOTICON => 'emoticon',
        self::T_HTML_ENTITY => 'htmlEntity',
        self::T_SHORTCODE => 'shortcode',
        self::T_UNICODE => 'unicode',
    ];

    /** @var Configuration|ConfigurationInterface */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
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
