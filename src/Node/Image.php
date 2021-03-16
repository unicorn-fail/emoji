<?php

declare(strict_types=1);

namespace League\Emoji\Node;

class Image extends Node implements EmojiContainerInterface
{
    /** @var Emoji */
    private $emoji;

    public function __construct(string $value, Emoji $emoji, string $url, ?string $alt = null, ?string $title = null)
    {
        parent::__construct($value);

        $this->emoji = $emoji;

        $this->setAttribute('src', $url);

        if ($alt !== null) {
            $this->setAttribute('alt', $alt);
        }

        if ($title !== null) {
            $this->setAttribute('title', $title);
        }
    }

    public function getEmoji(): Emoji
    {
        return $this->emoji;
    }

    public function getUrl(): string
    {
        return (string) $this->getAttribute('src', '');
    }

    public function setUrl(string $url): void
    {
        $this->setAttribute('src', $url);
    }
}
