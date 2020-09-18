<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Token;

use UnicornFail\Emoji\ConfigurationInterface;
use UnicornFail\Emoji\Emoji;
use UnicornFail\Emoji\Emojibase\DatasetInterface;
use UnicornFail\Emoji\Lexer;

abstract class AbstractEmojiToken extends AbstractToken
{
    /** @var string[]  */
    private $excludedShortcodes = [];

    /** @var Emoji */
    private $emoji;

    /** @var ?int */
    private $presentationMode = DatasetInterface::EMOJI;

    /** @var int */
    private $stringableType = Lexer::T_UNICODE;

    public function __construct(ConfigurationInterface $configuration, string $value, Emoji $emoji)
    {
        parent::__construct($value);
        $this->emoji = $emoji;

        /** @var string[] $excludedShortcodes */
        $excludedShortcodes = $configuration->get('exclude.shortcodes');
        $this->setExcludedShortcodes($excludedShortcodes);

        /** @var ?int $presentation */
        $presentation = $configuration->get('presentation');
        $this->setPresentationMode($presentation);

        $stringableType = (int) ($configuration->get('stringableType') ?? Lexer::T_UNICODE);
        $this->setStringableType($stringableType);
    }

    public function __toString(): string
    {
        $emoji = $this->getEmoji();
        switch ($this->stringableType) {
            case Lexer::T_EMOTICON:
                return $emoji->emoticon ?? $this->getValue();
            case Lexer::T_HTML_ENTITY:
                return $emoji->htmlEntity ?? $this->getValue();
            case Lexer::T_SHORTCODE:
                return $emoji->getShortcode($this->excludedShortcodes, true) ?? $this->getValue();
        }

        if (($this->presentationMode ?? $emoji->type) === DatasetInterface::TEXT) {
            return $emoji->text ?? $this->getValue();
        }

        return $emoji->emoji ?? $this->getValue();
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
