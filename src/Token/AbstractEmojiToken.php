<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Token;

use UnicornFail\Emoji\ConfigurationInterface;
use UnicornFail\Emoji\Emoji;
use UnicornFail\Emoji\EmojibaseInterface;
use UnicornFail\Emoji\Parser;

abstract class AbstractEmojiToken extends AbstractToken
{
    /** @var string[]  */
    private $excludedShortcodes = [];

    /** @var Emoji */
    private $emoji;

    /** @var ?int */
    private $presentationMode = EmojibaseInterface::EMOJI;

    /** @var int */
    private $stringableType = Parser::T_UNICODE;

    public function __construct(ConfigurationInterface $configuration, string $value, Emoji $emoji)
    {
        parent::__construct($value);
        $this->emoji = $emoji;
        $this->setExcludedShortcodes($configuration->get('excludeShortcodes'));
        $this->setPresentationMode($configuration->get('presentation'));
        $this->setStringableType($configuration->get('stringableType'));
    }

    public function __toString(): string
    {
        $emoji = $this->getEmoji();
        switch ($this->stringableType) {
            case Parser::T_EMOTICON:
                return $emoji->getEmoticon() ?? $this->getValue();
            case Parser::T_HTML_ENTITY:
                return $emoji->getHtmlEntity() ?? $this->getValue();
            case Parser::T_SHORTCODE:
                return $emoji->getShortcode($this->excludedShortcodes, true) ?? $this->getValue();
        }

        if (($this->presentationMode ?? $emoji->getType()) === EmojibaseInterface::TEXT) {
            return $emoji->getText() ?? $this->getValue();
        }

        return $emoji->getEmoji() ?? $this->getValue();
    }

    public function getEmoji(): Emoji
    {
        return $this->emoji;
    }

    /**
     * @param string[] $excludedShortcodes
     */
    public function setExcludedShortcodes(array $excludedShortcodes = []): self
    {
        $this->excludedShortcodes = $excludedShortcodes;

        return $this;
    }

    public function setPresentationMode(?int $presentationMode = null): self
    {
        $this->presentationMode = $presentationMode;

        return $this;
    }

    public function setStringableType(int $stringableType): self
    {
        $this->stringableType = $stringableType;

        return $this;
    }
}
