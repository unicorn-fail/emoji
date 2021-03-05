<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Node;

class Image extends AbstractStringContainer implements EmojiContainerInterface
{
    /** @var Emoji */
    private $emoji;

    public function __construct(string $value, Emoji $emoji, string $url, ?string $alt = null, ?string $title = null)
    {
        parent::__construct($value);

        $this->emoji = $emoji;

        $this->attributes->set('src', $url);

        if ($alt !== null) {
            $this->attributes->set('alt', $alt);
        }

        if ($title !== null) {
            $this->attributes->set('title', $title);
        }
    }

    public function getEmoji(): Emoji
    {
        return $this->emoji;
    }

    public function getUrl(): string
    {
        return (string) $this->attributes->get('src');
    }

    public function setUrl(string $url): void
    {
        $this->attributes->set('src', $url);
    }
}
