<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Emojibase\RegexInterface;
use UnicornFail\Emoji\Token\AbstractToken;

interface ParserInterface extends RegexInterface
{
    public const INDICES = ['emoji', 'emoticon', 'htmlEntity', 'shortcodes', 'text'];

    public function getConfiguration(): ConfigurationInterface;

    /**
     * @return AbstractToken[]
     */
    public function parse(string $input): array;
}
