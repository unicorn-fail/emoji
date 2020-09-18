<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Token;

use UnicornFail\Emoji\Emoji;

interface EmojiTokenInterface extends TokenInterface
{
    public function getEmoji(): Emoji;

    /**
     * @param string[] $excludedShortcodes
     */
    public function setExcludedShortcodes(array $excludedShortcodes = []): void;

    public function setPresentationMode(?int $presentationMode = null): void;

    public function setStringableType(int $stringableType): void;
}
