<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Environment\EmojiEnvironmentInterface;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Node\Inline\AbstractEmoji;
use UnicornFail\Emoji\Output\RenderedContentInterface;
use UnicornFail\Emoji\Parser\Lexer;
use UnicornFail\Emoji\Parser\Parser;
use UnicornFail\Emoji\Renderer\Renderer;

final class Converter
{
    /** @var EmojiEnvironmentInterface */
    private $environment;

    /** @var ?Parser */
    private $parser;

    /** @var ?Renderer */
    private $renderer;

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public function __construct(?iterable $configuration = null, ?EmojiEnvironmentInterface $environment = null)
    {
        if ($environment === null) {
            $environment = Environment::create($configuration);
        } elseif ($configuration !== null) {
            $environment->getConfiguration()->import((new \ArrayObject($configuration))->getArrayCopy());
        }

        $this->environment = $environment;
    }

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public static function create(?iterable $configuration = null): self
    {
        return new self($configuration);
    }

    /**
     * Converts CommonMark to HTML.
     *
     * @see Converter::convertToHtml
     *
     * @throws \RuntimeException
     */
    public function __invoke(string $commonMark): RenderedContentInterface
    {
        return $this->convert($commonMark);
    }

    protected function convert(string $input, ?string $type = null): RenderedContentInterface
    {
        $stringableType = (string) $this->environment->getConfiguration()->get('stringableType');

        // Parse.
        $document = $this->getParser()->parse($input);

        // Ensure tokens are set to the correct stringable type.
        if ($type !== null && $type !== $stringableType) {
            $walker = $document->walker();
            while ($event = $walker->next()) {
                if (! $event->isEntering()) {
                    continue;
                }

                $emoji = $event->getNode();
                if (! ($emoji instanceof AbstractEmoji)) {
                    continue;
                }

                $emoji->setStringableType($type);
            }
        }

        return $this->getRenderer()->renderDocument($document);
    }

    public function convertToEmoticon(string $input): RenderedContentInterface
    {
        return $this->convert($input, Lexer::EMOTICON);
    }

    public function convertToHtml(string $input): RenderedContentInterface
    {
        return $this->convert($input, Lexer::HTML_ENTITY);
    }

    public function convertToShortcode(string $input): RenderedContentInterface
    {
        return $this->convert($input, Lexer::SHORTCODE);
    }

    public function convertToUnicode(string $input): RenderedContentInterface
    {
        return $this->convert($input, Lexer::UNICODE);
    }

    public function getEnvironment(): EmojiEnvironmentInterface
    {
        return $this->environment;
    }

    public function getParser(): Parser
    {
        if ($this->parser === null) {
            $this->parser = new Parser($this->environment);
        }

        return $this->parser;
    }

    public function getRenderer(): Renderer
    {
        if ($this->renderer === null) {
            $this->renderer = new Renderer($this->environment);
        }

        return $this->renderer;
    }

    public function setParser(Parser $parser): void
    {
        $this->parser = $parser;
    }

    public function setRenderer(Renderer $renderer): void
    {
        $this->renderer = $renderer;
    }
}
