<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Token\AbstractEmojiToken;

final class Converter
{
    /** @var Configuration|ConfigurationInterface */
    private $configuration;

    /** @var Dataset */
    private $dataset;

    /** @var Parser|ParserInterface */
    private $parser;

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public function __construct(?iterable $configuration = null, ?Dataset $dataset = null, ?ParserInterface $parser = null)
    {
        $this->configuration = Configuration::create($configuration);
        $locale              = $this->configuration->get('locale');
        $preset              = $this->configuration->get('preset');
        $this->dataset       = $dataset ?? self::loadLocalePreset($locale, $preset);
        $this->parser        = $parser ?? new Parser($this->configuration, $this->dataset);
    }

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public static function create(?iterable $configuration = null): self
    {
        return new self($configuration);
    }

    public function convert(string $input, ?int $type = null): string
    {
        $stringableType = (int) $this->configuration->get('stringableType');

        // Parse.
        $tokens = $this->getParser()->parse($input);

        // Ensure tokens are set to the correct stringable type.
        if ($type !== null && $type !== $stringableType) {
            foreach (AbstractEmojiToken::filter($tokens) as $token) {
                $token->setStringableType($type);
            }
        }

        return \implode($tokens);
    }

    public function convertToEmoticon(string $input): string
    {
        return $this->convert($input, Lexer::T_EMOTICON);
    }

    public function convertToHtml(string $input): string
    {
        return $this->convert($input, Lexer::T_HTML_ENTITY);
    }

    public function convertToShortcode(string $input): string
    {
        return $this->convert($input, Lexer::T_SHORTCODE);
    }

    public function convertToUnicode(string $input): string
    {
        return $this->convert($input, Lexer::T_UNICODE);
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

    public function getParser(): ParserInterface
    {
        return $this->parser;
    }
}
