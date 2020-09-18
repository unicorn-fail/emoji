<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Token\AbstractEmojiToken;

final class Converter
{
    /** @var Parser|ParserInterface */
    private $parser;

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public function __construct(?iterable $configuration = null, ?Dataset $dataset = null, ?ParserInterface $parser = null)
    {
        $this->parser = $parser ?? new Parser($configuration, $dataset);
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
        $parser         = $this->getParser();
        $stringableType = (int) $parser->getConfiguration()->get('stringableType');

        // Parse.
        $tokens = $parser->parse($input);

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

    public function getParser(): ParserInterface
    {
        return $this->parser;
    }
}
