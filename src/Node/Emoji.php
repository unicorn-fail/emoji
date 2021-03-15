<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Node;

use UnicornFail\Emoji\Dataset\Dataset;
use UnicornFail\Emoji\Dataset\Emoji as DatasetEmoji;
use UnicornFail\Emoji\EmojiConverter;
use UnicornFail\Emoji\Emojibase\EmojibaseSkinsInterface;

/**
 * @property ?string $annotation
 * @property ?string $emoji
 * @property ?string $emoticon
 * @property ?int $gender
 * @property ?int $group
 * @property ?string $hexcode
 * @property ?string $htmlEntity
 * @property ?int $order
 * @property ?string $shortcode
 * @property string[] $shortcodes
 * @property Dataset $skins
 * @property ?int $subgroup
 * @property string[] $tags
 * @property ?string $text
 * @property int[] $tone
 * @property int $type
 * @property ?string $unicode
 * @property ?float $version
 *
 * @method string|null getShortcode(?array $exclude = null, bool $wrap = false)
 * @method string[] getShortcodes(?array $exclude = null)
 * @method string|\Stringable render()
 * @method void setRenderer(callable $renderer)
 */
final class Emoji extends Node
{
    /** @var DatasetEmoji */
    private $datasetEmoji;

    /** @var int */
    private $parsedType;

    /** @var string */
    private $parsedValue;

    public function __construct(int $parsedType, string $parsedValue, DatasetEmoji $emoji)
    {
        parent::__construct($parsedValue);
        $this->datasetEmoji = $emoji;
        $this->parsedType   = $parsedType;
        $this->parsedValue  = $parsedValue;
    }

    /**
     * @param mixed[] $arguments
     *
     * @return ?mixed
     */
    public function __call(string $name, array $arguments)
    {
        /** @var callable $method */
        $method = [$this->datasetEmoji, $name];

        return \call_user_func_array($method, $arguments);
    }

    /** @return ?mixed */
    public function __get(string $name)
    {
        return $this->datasetEmoji->$name;
    }

    public function getParsedType(): int
    {
        return $this->parsedType;
    }

    public function getParsedValue(): string
    {
        return $this->parsedValue;
    }

    public function getSkin(int $tone = EmojibaseSkinsInterface::LIGHT_SKIN): ?self
    {
        $skin = $this->datasetEmoji->getSkin($tone);

        if ($skin === null) {
            return null;
        }

        $property = (string) (EmojiConverter::TYPES[$this->parsedType] ?? EmojiConverter::UNICODE);
        $value    = (string) ($skin->$property ?? $this->parsedValue);

        return new self($this->parsedType, $value, $skin);
    }
}
