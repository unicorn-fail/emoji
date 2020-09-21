<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Parser;

use Doctrine\Common\Lexer\AbstractLexer;
use UnicornFail\Emoji\Emojibase\RegexInterface;
use UnicornFail\Emoji\Environment\EmojiEnvironmentInterface;

class Lexer extends AbstractLexer implements RegexInterface
{
    public const T_TEXT = 0;

    public const T_EMOTICON = 1;

    public const T_HTML_ENTITY = 2;

    public const T_SHORTCODE = 3;

    public const T_UNICODE = 4;

    public const TEXT        = 'text';
    public const EMOTICON    = 'emoticon';
    public const HTML_ENTITY = 'html_entity';
    public const SHORTCODE   = 'shortcode';
    public const UNICODE     = 'unicode';

    public const TYPES = [
        self::T_TEXT => self::TEXT,
        self::T_EMOTICON => self::EMOTICON,
        self::T_HTML_ENTITY => self::HTML_ENTITY,
        self::T_SHORTCODE => self::SHORTCODE,
        self::T_UNICODE => self::UNICODE,
    ];

    /** @var EmojiEnvironmentInterface */
    private $environment;

    public function __construct(EmojiEnvironmentInterface $environment)
    {
        $this->environment = $environment;
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
            $this->environment->getConfiguration()->get('native') ? self::SHORTCODE_NATIVE_REGEX : self::SHORTCODE_REGEX,
        ];
        if ($this->environment->getConfiguration()->get('convertEmoticons')) {
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
        if (\preg_match($this->environment->getConfiguration()->get('native') ? self::SHORTCODE_NATIVE_REGEX : self::SHORTCODE_REGEX, $value)) {
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
