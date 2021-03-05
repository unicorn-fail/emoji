<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Parser;

use UnicornFail\Emoji\Environment\EnvironmentInterface;
use UnicornFail\Emoji\Event\DocumentParsedEvent;
use UnicornFail\Emoji\Event\DocumentPreParsedEvent;
use UnicornFail\Emoji\Input\Input;
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Node\Emoji;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Node\Text;

final class EmojiParser implements EmojiParserInterface
{
    public const INDICES = [
        Lexer::T_EMOTICON    => 'emoticon',
        Lexer::T_HTML_ENTITY => 'htmlEntity',
        Lexer::T_SHORTCODE   => 'shortcodes',
        Lexer::T_UNICODE     => 'unicode',
    ];

    /** @var EnvironmentInterface */
    private $environment;

    /** @var Lexer */
    private $lexer;

    public function __construct(EnvironmentInterface $environment, ?Lexer $lexer = null)
    {
        $this->environment = $environment;
        $this->lexer       = $lexer ?? new Lexer($environment);
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

            switch ($type) {
                case Lexer::T_TEXT:
                    $node = $this->parseText($value);
                    break;

                default:
                    $node = $this->parseEmoji($type, $value);
            }

            if ($node instanceof Node) {
                $document->appendChild($node);
            }
        }
    }

    protected function parseEmoji(int $type, string $value): ?Emoji
    {
        // Immediately return if not a valid type.
        if (! isset(self::INDICES[$type])) {
            return null;
        }

        // Return if no emoji could be found.
        if (! ($emoji = $this->environment->getRuntimeDataset(self::INDICES[$type])->offsetGet($value))) {
            return null;
        }

        return new Emoji($type, $value, $emoji);
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

        if (! $text) {
            return null;
        }

        return new Text($text);
    }
}
