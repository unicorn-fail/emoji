<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Dataset;

use UnicornFail\Emoji\Emojibase\EmojibaseDatasetInterface;
use UnicornFail\Emoji\Emojibase\EmojibaseSkinsInterface;
use UnicornFail\Emoji\Util\ImmutableArrayIterator;
use UnicornFail\Emoji\Util\Normalize;

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
 */
final class Emoji extends ImmutableArrayIterator implements \JsonSerializable, \Stringable
{
    public const PROPERTY_TYPES = [
        'annotation' => '!?string',
        'emoji'      => '!?string',
        'emoticon'   => '!?string',
        'gender'     => '?int',
        'group'      => '?int',
        'hexcode'    => '!?string',
        'order'      => '?int',
        'shortcodes' => 'string[]<\UnicornFail\Emoji\Util\Normalize::shortcodes>',
        'skins'      => '\UnicornFail\Emoji\Dataset\Dataset',
        'subgroup'   => '?int',
        'tags'       => 'string[]',
        'text'       => '!?string',
        'tone'       => 'int[]',
        'type'       => 'int',
        'version'    => '!?float',
    ];

    /**
     * @var callable
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $renderer = '\UnicornFail\Emoji\Dataset\Emoji::renderProperty';

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data = [])
    {
        $data = Normalize::properties($data, self::PROPERTY_TYPES);

        /** @var ?string $hexcode */
        $hexcode = $data['hexcode'] ?? null;

        /** @var ?string $emoji */
        $emoji = $data['emoji'] ?? null;

        /** @var ?string $text */
        $text = $data['text'] ?? null;

        $type = (int) ($data['type'] ?? EmojibaseDatasetInterface::EMOJI);

        $data['htmlEntity'] = null;
        if ($hexcode !== null) {
            $data['htmlEntity'] = '&#x' . \implode(';&#x', \explode('-', $hexcode)) . ';';
        }

        $data['unicode'] = $text;
        if ($type === EmojibaseDatasetInterface::EMOJI && $emoji) {
            $data['unicode'] = $emoji;
        }

        parent::__construct($data);
    }

    public static function renderProperty(Emoji $emoji, string $property = 'unicode'): string
    {
        return (string) ($emoji->$property ?? '');
    }

    public function __toString(): string
    {
        return (string) $this->render();
    }

    /**
     * @param string[]|null $exclude
     */
    public function getShortcode(?array $exclude = null, bool $wrap = false): ?string
    {
        $shortcode = \current($this->getShortcodes($exclude)) ?: null;

        if ($shortcode !== null && $wrap) {
            $shortcode = \sprintf(':%s:', $shortcode);
        }

        return $shortcode;
    }

    /**
     * @param ?string[] $exclude
     *
     * @return string[]
     */
    public function getShortcodes(?array $exclude = null): array
    {
        if ($exclude !== null) {
            return \array_diff($this->shortcodes, $exclude);
        }

        return $this->shortcodes;
    }

    public function getSkin(int $tone = EmojibaseSkinsInterface::LIGHT_SKIN): ?self
    {
        /** @var ?static $skin */
        $skin = \current($this->skins->filter(static function (Emoji $emoji) use ($tone) {
            return \in_array($tone, (array) $emoji->tone, true);
        })->getArrayCopy()) ?: null;

        return $skin;
    }

    /** {@inheritDoc} */
    public function jsonSerialize(): string
    {
        return (string) $this->render();
    }

    /** @return \Stringable|string */
    public function render()
    {
        /** @var \Stringable|string|null $rendered */
        $rendered = \call_user_func_array($this->renderer, [$this]);

        if ($rendered instanceof \Stringable) {
            return $rendered;
        }

        return (string) ($rendered ?? '');
    }

    public function setRenderer(callable $renderer): void
    {
        $this->renderer = $renderer;
    }
}
