<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Parser;

use UnicornFail\Emoji\Environment\EmojiEnvironmentInterface;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Event\DocumentParsedEvent;
use UnicornFail\Emoji\Event\DocumentPreParsedEvent;
use UnicornFail\Emoji\Input\Input;
use UnicornFail\Emoji\Node\Block\Document;
use UnicornFail\Emoji\Node\Inline\Emoticon;
use UnicornFail\Emoji\Node\Inline\HtmlEntity;
use UnicornFail\Emoji\Node\Inline\Shortcode;
use UnicornFail\Emoji\Node\Inline\Text;
use UnicornFail\Emoji\Node\Inline\Unicode;

class Parser implements ParserInterface
{
    public const T_DATASETS = [
        Lexer::T_EMOTICON => 'emoticon',
        Lexer::T_HTML_ENTITY => 'htmlEntity',
        Lexer::T_SHORTCODE => 'shortcodes',
        Lexer::T_UNICODE => 'unicode',
    ];

    /** @var EmojiEnvironmentInterface  */
    private $environment;

    /** @var Lexer */
    private $lexer;

    public function __construct(?EmojiEnvironmentInterface $environment, ?Lexer $lexer = null)
    {
        $this->environment = $environment ?? Environment::create();
        $this->lexer       = $lexer ?? new Lexer($this->environment);
    }

    public function parse(string $input): Document
    {
        $preParsedEvent = new DocumentPreParsedEvent(new Document(), new Input($input));
        $this->environment->dispatch($preParsedEvent);

        $document = $preParsedEvent->getDocument();
        $input    = $preParsedEvent->getInput();

        $lineCount = $input->getLineCount();
        foreach ($input->getLines() as $lineNumber => $line) {
            $this->parseLine($line, $document);

            if ($lineNumber < $lineCount) {
                $document->appendChild(new Text("\n"));
            }
        }

        // If the original content ended with a new line, mimic the same.
        if (\preg_match_all('/.*\n|\r\n$/sm', $input->getContent()) === 1) {
            $document->appendChild(new Text("\n"));
        }

        $this->environment->dispatch(new DocumentParsedEvent($document));

        return $document;
    }

    protected function parseLine(string $line, Document $document): void
    {
        $this->lexer->setInput($line);
        $this->lexer->moveNext();

        while (true) {
            if (! $this->lexer->lookahead) {
                break;
            }

            $this->lexer->moveNext();

            $type  = (int) ($this->lexer->token['type'] ?? Lexer::T_TEXT);
            $value = (string) ($this->lexer->token['value'] ?? '');

            if ($node = $type === Lexer::T_TEXT ? $this->parseText($value) : $this->parseEmoji($type, $value)) {
                $document->appendChild($node);
            }
        }
    }

    /**
     * @return Emoticon|HtmlEntity|Shortcode|Unicode|null
     */
    protected function parseEmoji(int $type, string $value)
    {
        // Immediately return if not a valid type.
        if (! isset(self::T_DATASETS[$type])) {
            return null;
        }

        // Clone the configuration here. This is necessary so it can be passed to tokens,
        // which may be rendered at a later time; when the configuration may have changed.
        $environment = clone $this->environment;
        $dataset     = $this->environment->getDataset()->indexBy(self::T_DATASETS[$type]);
        $emoji       = $dataset->offsetGet($value);

        // Return if not an emoji.
        if (! $emoji) {
            return null;
        }

        switch ($type) {
            case Lexer::T_EMOTICON:
                return new Emoticon($value, $emoji, $environment);
            case Lexer::T_HTML_ENTITY:
                return new HtmlEntity($value, $emoji, $environment);
            case Lexer::T_SHORTCODE:
                return new Shortcode($value, $emoji, $environment);
            case Lexer::T_UNICODE:
                return new Unicode($value, $emoji, $environment);
        }

        return null;
    }

    protected function parseText(string $value): ?Text
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
