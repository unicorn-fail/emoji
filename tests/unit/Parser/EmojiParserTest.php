<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Parser;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Exception\UnexpectedEncodingException;
use UnicornFail\Emoji\Lexer\EmojiLexer;
use UnicornFail\Emoji\Parser\EmojiParser;

class EmojiParserTest extends TestCase
{
    /** @var Environment */
    protected $environment;

    protected function setUp(): void
    {
        $this->environment = Environment::create();
    }

    public function testCustomLexer(): void
    {
        $lexer  = $this->createMock(EmojiLexer::class);
        $parser = new EmojiParser($this->environment, $lexer);
        $this->assertSame($lexer, $parser->getLexer());
    }

    public function testInvalidContent(): void
    {
        $parser = new EmojiParser($this->environment);

        $this->expectException(UnexpectedEncodingException::class);

        $parser->parse(\chr(250));
    }

    public function testEmptyString(): void
    {
        $parser = new EmojiParser($this->environment);

        $document = $parser->parse('');

        $this->assertCount(0, $document->getNodes());
    }

    public function testInvalidEmoji(): void
    {
        $parser = new EmojiParser($this->environment);

        $document = $parser->parse(':foo-bar-baz: ');

        $this->assertCount(1, $document->getNodes());
    }
}
