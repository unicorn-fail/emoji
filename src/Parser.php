<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Token\EmojiTokenInterface;
use UnicornFail\Emoji\Token\Text;
use UnicornFail\Emoji\Token\TokenInterface;

class Parser implements ParserInterface
{
    public const T_EMOJI_TOKENS = [
        Lexer::T_EMOTICON => '\UnicornFail\Emoji\Token\Emoticon',
        Lexer::T_HTML_ENTITY => 'UnicornFail\Emoji\Token\HtmlEntity',
        Lexer::T_SHORTCODE => 'UnicornFail\Emoji\Token\Shortcode',
        Lexer::T_UNICODE => 'UnicornFail\Emoji\Token\Unicode',
    ];

    public const T_DATASETS = [
        Lexer::T_EMOTICON => 'emoticon',
        Lexer::T_HTML_ENTITY => 'htmlEntity',
        Lexer::T_SHORTCODE => 'shortcodes',
        Lexer::T_UNICODE => 'unicode',
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

        $locale = $this->configuration->get('locale');
        \assert(\is_string($locale));

        /** @var string[] $preset */
        $preset = $this->configuration->get('preset');

        $this->dataset = $dataset ?? self::loadLocalePreset($locale, $preset);
        $this->lexer   = $lexer ?? new Lexer($this->configuration);
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
     * @return TokenInterface[]
     */
    public function parse(string $input): array
    {
        $this->lexer->setInput($input);
        $this->lexer->moveNext();

        return $this->parseTokens();
    }

    /**
     * @return TokenInterface[]
     */
    protected function parseTokens(): array
    {
        $tokens = [];
        while (true) {
            if (! $this->lexer->lookahead) {
                break;
            }

            $this->lexer->moveNext();

            $type  = (int) ($this->lexer->token['type'] ?? Lexer::T_TEXT);
            $value = (string) ($this->lexer->token['value'] ?? '');

            if ($token = $type === Lexer::T_TEXT ? $this->parseTextToken($value) : $this->parseToken($type, $value)) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    protected function parseToken(int $type, string $value): ?EmojiTokenInterface
    {
        $token = null;

        // Immediately return if not a valid type.
        if (isset(self::T_DATASETS[$type]) || isset(self::T_EMOJI_TOKENS[$type])) {
            $dataset = $this->dataset->indexBy(self::T_DATASETS[$type]);

            $tokenClass = self::T_EMOJI_TOKENS[$type];

            if ($emoji = $dataset->offsetGet($value)) {
                // Clone the configuration here. This is necessary so it can be passed to tokens,
                // which may be rendered at a later time; when the configuration may have changed.
                /** @var EmojiTokenInterface $token */
                $token = new $tokenClass($value, clone $this->configuration, $emoji);
            }
        }

        return $token;
    }

    protected function parseTextToken(string $value): ?Text
    {
        $text = '';
        while (true) {
            $text .= $value;
            if ($this->lexer->lookahead === null || $this->lexer->lookahead['type'] !== Lexer::T_TEXT) {
                break;
            }

            $value = (string) ($this->lexer->lookahead['value'] ?? '');

            $this->lexer->moveNext();
        }

        return $text
            ? new Text($text)
            : null;
    }
}
