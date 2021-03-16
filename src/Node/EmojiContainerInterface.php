<?php

declare(strict_types=1);

namespace League\Emoji\Node;

interface EmojiContainerInterface
{
    public function getEmoji(): Emoji;
}
