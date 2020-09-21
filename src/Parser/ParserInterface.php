<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Parser;

use UnicornFail\Emoji\Emojibase\RegexInterface;
use UnicornFail\Emoji\Node\Block\Document;

interface ParserInterface extends RegexInterface
{
    public const INDICES = ['emoticon', 'htmlEntity', 'shortcodes', 'unicode'];

    public function parse(string $input): Document;
}
