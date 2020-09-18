<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Emojibase\DatasetInterface;
use UnicornFail\Emoji\Emojibase\SkinsInterface;
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
 * @property ?float $version
 */
final class Emoji extends ImmutableArrayIterator implements \Stringable
{
    public const PROPERTY_TYPES = [
        'annotation' => '!?string',
        'emoji' => '!?string',
        'emoticon' => '!?string',
        'gender' => '?int',
        'group' => '?int',
        'hexcode' => '!?string',
        'order' => '?int',
        'shortcodes' => 'string[]<\UnicornFail\Emoji\Util\Normalize::shortcodes>',
        'skins' => '\UnicornFail\Emoji\Dataset',
        'subgroup' => '?int',
        'tags' => 'string[]',
        'text' => '!?string',
        'tone' => 'int[]',
        'type' => 'int',
        'version' => '!?float',
    ];

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct(Normalize::properties($data, self::PROPERTY_TYPES));
    }

    public function __toString(): string
    {
        return $this->getUnicode() ?: '';
    }

    public function getHtmlEntity(): ?string
    {
        $hexcode = $this->hexcode;

        return $hexcode ? '&#x' . \implode(';&#x', \explode('-', $hexcode)) . ';' : null;
    }

    /**
     * @param string[]|null $exclude
     */
    public function getShortcode(?array $exclude = null, bool $wrap = false): ?string
    {
        $shortcode = \current($this->getShortcodes($exclude));

        if ($wrap && $shortcode) {
            $shortcode = \sprintf(':%s:', $shortcode);
        }

        return $shortcode ?: null;
    }

    /**
     * @param ?string[] $exclude
     *
     * @return string[]
     */
    public function getShortcodes(?array $exclude = null): array
    {
        /** @var string[] $shortcodes */
        $shortcodes = (array) $this->offsetGet('shortcodes');

        return $exclude ? \array_diff($shortcodes, $exclude) : $shortcodes;
    }

    public function getSkin(int $tone = SkinsInterface::LIGHT_SKIN): ?self
    {
        /** @var ?static $skin */
        $skin = \current(
            $this->skins->filter(
                static function (Emoji $emoji) use ($tone) {
                    return \in_array($tone, (array) $emoji->tone, true);
                }
            )->getArrayCopy()
        ) ?: null;

        return $skin;
    }

    public function getUnicode(): ?string
    {
        return $this->type === DatasetInterface::EMOJI && ($emoji = $this->emoji) ? $emoji : $this->text;
    }
}
