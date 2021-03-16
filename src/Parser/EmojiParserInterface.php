<?php

declare(strict_types=1);

namespace League\Emoji\Parser;

use League\Emoji\Emojibase\EmojibaseRegexInterface;
use League\Emoji\Node\Document;

interface EmojiParserInterface extends EmojibaseRegexInterface
{
    public function parse(string $input): Document;
}
