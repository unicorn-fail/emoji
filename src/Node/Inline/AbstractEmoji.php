<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Node\Inline;

use UnicornFail\Emoji\Dataset\Emoji;
use UnicornFail\Emoji\Emojibase\DatasetInterface;
use UnicornFail\Emoji\Environment\ConfigurableEnvironmentInterface;
use UnicornFail\Emoji\Parser\Lexer;

abstract class AbstractEmoji extends Text
{
    /** @var string[]  */
    private $excludedShortcodes = [];

    /** @var Emoji */
    private $emoji;

    /** @var ?int */
    private $presentationMode = DatasetInterface::EMOJI;

    /** @var string */
    private $stringableType = Lexer::UNICODE;

    public function __construct(string $value, Emoji $emoji, ConfigurableEnvironmentInterface $environment)
    {
        parent::__construct($value);
        $this->emoji = $emoji;

        /** @var string[] $excludedShortcodes */
        $excludedShortcodes = $environment->getConfiguration()->get('exclude.shortcodes');
        $this->setExcludedShortcodes($excludedShortcodes);

        /** @var ?int $presentation */
        $presentation = $environment->getConfiguration()->get('presentation');
        $this->setPresentationMode($presentation);

        $stringableType = (string) ($environment->getConfiguration()->get('stringableType') ?? Lexer::UNICODE);
        $this->setStringableType($stringableType);
    }

    public function getEmoji(): Emoji
    {
        return $this->emoji;
    }

    public function getLiteral(): string
    {
        $value = parent::getLiteral();

        $emoji = $this->getEmoji();
        switch ($this->stringableType) {
            case Lexer::EMOTICON:
                return $emoji->emoticon ?? $value;
            case Lexer::HTML_ENTITY:
                return $emoji->htmlEntity ?? $value;
            case Lexer::SHORTCODE:
                return $emoji->getShortcode($this->excludedShortcodes, true) ?? $value;
        }

        if (($this->presentationMode ?? $emoji->type) === DatasetInterface::TEXT) {
            return $emoji->text ?? $value;
        }

        return $emoji->emoji ?? $value;
    }

    /**
     * @param string[] $excludedShortcodes
     */
    public function setExcludedShortcodes(array $excludedShortcodes = []): void
    {
        $this->excludedShortcodes = $excludedShortcodes;
    }

    public function setPresentationMode(?int $presentationMode = null): void
    {
        $this->presentationMode = $presentationMode;
    }

    public function setStringableType(string $stringableType): void
    {
        $this->stringableType = $stringableType;
    }
}
