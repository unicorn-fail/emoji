<?php

declare(strict_types=1);

namespace League\Emoji\Lexer;

use Doctrine\Common\Lexer\AbstractLexer;
use League\Emoji\Emojibase\EmojibaseRegexInterface;
use League\Emoji\Environment\EnvironmentInterface;
use League\Emoji\Exception\UnexpectedEncodingException;

class EmojiLexer extends AbstractLexer implements EmojibaseRegexInterface
{
    public const T_TEXT = 0;

    public const T_EMOTICON = 1;

    public const T_HTML_ENTITY = 2;

    public const T_SHORTCODE = 3;

    public const T_UNICODE = 4;

    public const TYPES = [
        self::T_TEXT,
        self::T_EMOTICON,
        self::T_HTML_ENTITY,
        self::T_SHORTCODE,
        self::T_UNICODE,
    ];

    /** @var EnvironmentInterface */
    private $environment;

    public function __construct(EnvironmentInterface $environment)
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
        $config = $this->environment->getConfiguration();

        $patterns = [];

        if ($config->get('convert.unicode')) {
            $patterns[] = self::CODEPOINT_EMOJI_LOOSE_REGEX;
        }

        if ($config->get('convert.htmlEntity')) {
            $patterns[] = self::HTML_ENTITY_REGEX;
        }

        if ($config->get('convert.shortcode')) {
            $patterns[] = $this->environment->getRuntimeDataset()->isNative()
                ? self::SHORTCODE_NATIVE_REGEX
                : self::SHORTCODE_REGEX;
        }

        if ($config->get('convert.emoticon')) {
            $patterns[] = self::EMOTICON_REGEX;
        }

        return static::cleanPatterns($patterns);
    }

    /**
     * @param string[] $patterns
     *
     * @return string[]
     */
    protected static function cleanPatterns(array $patterns): array
    {
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
        if (\preg_match($this->environment->getRuntimeDataset()->isNative() ? self::SHORTCODE_NATIVE_REGEX : self::SHORTCODE_REGEX, $value)) {
            return self::T_SHORTCODE;
        }

        if (\preg_match(self::EMOTICON_REGEX, $value)) {
            return self::T_EMOTICON;
        }

        if (\preg_match(self::CODEPOINT_EMOJI_LOOSE_REGEX, $value)) {
            return self::T_UNICODE;
        }

        if (\preg_match(self::HTML_ENTITY_REGEX, $value)) {
            return self::T_HTML_ENTITY;
        }

        return self::T_TEXT;
    }

    /**
     * {@inheritDoc}
     */
    public function setInput($input): void
    {
        if (! \mb_check_encoding($input, 'UTF-8')) {
            throw new UnexpectedEncodingException('Unexpected encoding - UTF-8 or ASCII was expected');
        }

        parent::setInput($input);
    }
}
