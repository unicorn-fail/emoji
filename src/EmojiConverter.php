<?php

declare(strict_types=1);

namespace League\Emoji;

use League\Emoji\Environment\Environment;
use League\Emoji\Environment\EnvironmentInterface;
use League\Emoji\Parser\EmojiParser;
use League\Emoji\Parser\EmojiParserInterface;
use League\Emoji\Renderer\DocumentRenderer;
use League\Emoji\Renderer\DocumentRendererInterface;

class EmojiConverter implements EmojiConverterInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    /** @var EmojiParserInterface */
    private $parser;

    /** @var DocumentRendererInterface */
    private $renderer;

    public function __construct(EnvironmentInterface $environment, ?EmojiParserInterface $parser = null, ?DocumentRendererInterface $renderer = null)
    {
        $this->environment = $environment;
        $this->parser      = $parser ?? new EmojiParser($environment);
        $this->renderer    = $renderer ?? new DocumentRenderer($environment);
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function create(array $config = []): EmojiConverterInterface
    {
        return new self(Environment::create($config));
    }

    /**
     * Converts all HTML entities, shortcodes or emoticons to emojis (unicode).
     *
     * @see EmojiConverterInterface::convert
     *
     * @throws \RuntimeException
     */
    public function __invoke(string $input): string
    {
        return $this->convert($input);
    }

    public function convert(string $input): string
    {
        $document = $this->parser->parse($input);

        return $this->renderer->renderDocument($document);
    }

    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    public function getParser(): EmojiParserInterface
    {
        return $this->parser;
    }

    public function getRenderer(): DocumentRendererInterface
    {
        return $this->renderer;
    }
}
