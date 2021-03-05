<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Node;

interface EmojiContainerInterface
{
    public function getEmoji(): Emoji;
}
