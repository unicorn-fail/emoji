<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Parser;

use UnicornFail\Emoji\Emojibase\EmojibaseRegexInterface;
use UnicornFail\Emoji\Node\Document;

interface EmojiParserInterface extends EmojibaseRegexInterface
{
    public function parse(string $input): Document;
}
