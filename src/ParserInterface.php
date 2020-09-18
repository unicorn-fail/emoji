<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Emojibase\RegexInterface;
use UnicornFail\Emoji\Token\TokenInterface;

interface ParserInterface extends RegexInterface
{
    public const INDICES = ['emoticon', 'htmlEntity', 'shortcodes', 'unicode'];

    public function getConfiguration(): ConfigurationInterface;

    /**
     * @return TokenInterface[]
     */
    public function parse(string $input): array;
}
