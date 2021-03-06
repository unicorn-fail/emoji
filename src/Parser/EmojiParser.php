<?php

declare(strict_types=1);

namespace League\Emoji\Parser;

use League\Emoji\Dataset\Emoji as DatasetEmoji;
use League\Emoji\Environment\EnvironmentInterface;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Event\DocumentPreParsedEvent;
use League\Emoji\Lexer\EmojiLexer;
use League\Emoji\Node\Document;
use League\Emoji\Node\Emoji;
use League\Emoji\Node\Node;
use League\Emoji\Node\Text;

final class EmojiParser implements EmojiParserInterface
{
    public const INDICES = [
        EmojiLexer::T_EMOTICON    => 'emoticon',
        EmojiLexer::T_HTML_ENTITY => 'htmlEntity',
        EmojiLexer::T_SHORTCODE   => 'shortcodes',
        EmojiLexer::T_UNICODE     => 'unicode',
    ];

    /** @var EnvironmentInterface */
    private $environment;

    /** @var EmojiLexer */
    private $lexer;

    public function __construct(EnvironmentInterface $environment, ?EmojiLexer $lexer = null)
    {
        $this->environment = $environment;
        $this->lexer       = $lexer ?? new EmojiLexer($environment);
    }

    public function getLexer(): EmojiLexer
    {
        return $this->lexer;
    }

    public function parse(string $input): Document
    {
        $preParsedEvent = new DocumentPreParsedEvent(new Document(), $input);
        $this->environment->dispatch($preParsedEvent);

        $document = $preParsedEvent->getDocument();
        $input    = $preParsedEvent->getInput();

        $this->lexer->setInput($input);
        $this->lexer->moveNext();

        while (true) {
            if (! $this->lexer->lookahead) {
                break;
            }

            $this->lexer->moveNext();

            $node = $this->parseToken();

            $document->appendNode($node);
        }

        $this->environment->dispatch(new DocumentParsedEvent($document));

        return $document;
    }

    protected function parseToken(): Node
    {
        /** @var array<string, mixed> $token */
        $token = $this->lexer->token;

        $value = '';
        if (((string) $token['value']) !== '') {
            $value = (string) ($token['value'] ?? '');
        }

        $type = (int) $token['type'];

        $node = null;
        if ($type !== EmojiLexer::T_TEXT && \in_array($type, EmojiLexer::TYPES, true)) {
            $node = $this->parseEmoji($type, $value);
        }

        if ($node === null) {
            $node = $this->parseText($value);
        }

        return $node;
    }

    protected function parseEmoji(int $type, string $value): ?Emoji
    {
        $dataset = $this->environment->getRuntimeDataset(self::INDICES[$type]);

        try {
            /** @var DatasetEmoji $emoji */
            $emoji = $dataset->offsetGet($value);

            return new Emoji($type, $value, $emoji);
        } catch (\OutOfRangeException $exception) {
            return null;
        }
    }

    protected function parseText(string $value): Text
    {
        $text = '';
        while (true) {
            $text .= $value;
            if ($this->lexer->lookahead === null || $this->lexer->lookahead['type'] !== EmojiLexer::T_TEXT) {
                break;
            }

            $value = (string) ($this->lexer->lookahead['value'] ?? '');

            $this->lexer->moveNext();
        }

        return new Text($text);
    }
}
