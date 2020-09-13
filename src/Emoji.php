<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

final class Emoji implements \ArrayAccess, \IteratorAggregate, \Stringable
{
    /**
     * A localized description, provided by CLDR, primarily used for text-to-speech (TTS) and accessibility.
     *
     * @var ?string
     */
    private $annotation;

    /**
     * The emoji presentation Unicode character.
     *
     * @var ?string
     */
    private $emoji;

    /**
     * If applicable, an emoticon representing the emoji character.
     *
     * @var ?string
     */
    private $emoticon;

    /**
     * If applicable, the gender of the emoji character. 0 for female, 1 for male.
     *
     * @var ?int
     */
    private $gender;

    /**
     * The categorical group the emoji belongs to, ranging from 0 (smileys) to 7 (flags); NULL for uncategorized.
     *
     * @var ?int
     */
    private $group;

    /**
     * The hexadecimal representation of the emoji Unicode codepoint.
     *
     * If the emoji supports both emoji and text variations, the hexcode will not include the variation selector.
     * If a multi-person, multi-gender, or skin tone variation, the hexcode will include zero width joiners
     * and variation selectors.
     *
     * @var ?string
     */
    private $hexcode;

    /**
     * The order in which emoji should be displayed on a device, through a keyboard or emoji picker; NULL for unordered.
     *
     * @var int
     */
    private $order;

    /**
     * An array of community curated shortcodes. Does not include surrounding colons.
     *
     * @var string[]
     */
    private $shortcodes;

    /**
     * An array of emoji objects for each skin tone modification, starting at light skin, and ending with dark skin.
     *
     * @var Dataset
     */
    private $skins;

    /**
     * The categorical subgroup the emoji belongs to, ranging from 0 to 75; NULL for uncategorized.
     *
     * @var ?int
     */
    private $subgroup;

    /**
     * An array of localized keywords, provided by CLDR, to use for searching and filtering.
     *
     * @var string[]
     */
    private $tags;

    /**
     * The text presentation Unicode character.
     *
     * @var ?string
     */
    private $text;

    /**
     * If applicable, the skin tone of the emoji character.
     *
     * 1 for light skin, 2 for medium-light skin, 3 for medium skin, 4 for medium-dark skin, and 5 for dark skin.
     * Multi-person skin tones will be an array of values.
     *
     * @var int[]
     */
    private $tone;

    /**
     * The default presentation of the emoji character. 0 for text, 1 for emoji.
     *
     * @var int
     */
    private $type;

    /**
     * The version in which the emoji character was released.
     *
     * @var ?float
     */
    private $version;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data = [])
    {
        $data = self::normalizeProperties($data);

        $this->annotation = $data['annotation'] ?? null;
        $this->emoji      = $data['emoji'] ?? null;
        $this->emoticon   = $data['emoticon'] ?? null;
        $this->gender     = $data['gender'] ?? null;
        $this->group      = $data['group'] ?? null;
        $this->hexcode    = $data['hexcode'] ?? null;
        $this->order      = $data['order'] ?? null;
        $this->shortcodes = $data['shortcodes'] ?? [];
        $this->skins      = $data['skins'] ?? new Dataset();
        $this->subgroup   = $data['subgroup'] ?? null;
        $this->tags       = $data['tags'] ?? [];
        $this->text       = $data['text'] ?? null;
        $this->tone       = $data['tone'] ?? [];
        $this->type       = $data['type'] ?? EmojibaseInterface::EMOJI;
        $this->version    = $data['version'] ?? null;
    }

    /**
     * @return string[]
     */
    public function __sleep(): array
    {
        $properties = [];
        foreach (\get_object_vars($this) as $property => $value) {
            if (isset($value)) {
                $properties[] = $property;
            }
        }

        return $properties;
    }

    /**
     * @param mixed $data
     *
     * @return Emoji
     */
    public static function create($data): self
    {
        if ($data instanceof self) {
            return $data;
        }

        return new self($data);
    }

    /**
     * @param mixed[] $properties
     *
     * @return mixed[]
     */
    protected static function normalizeProperties(array $properties = []): array
    {
        foreach ($properties as $key => $value) {
            $properties[$key] = static::normalizePropertyValue($key, $value);
        }

        return $properties;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected static function normalizePropertyValue(string $key, $value)
    {
        switch ($key) {
            case 'gender':
            case 'group':
            case 'order':
            case 'subgroup':
            case 'type':
                return (int) $value;
            case 'tags':
                return \array_map('strval', (array) $value);
            case 'tone':
                return \array_map('intval', (array) $value);
            case 'version':
                return (float) $value;
            case 'shortcodes':
                return self::normalizeShortcodes($value);
            case 'skins':
                return new Dataset($value);
        }

        return (string) $value ?: null;
    }

    /**
     * @param string|string[] $shortcode
     *
     * @return string[]
     */
    public static function normalizeShortcodes($shortcode): array
    {
        $normalized = [];
        foreach (\func_get_args() as $shortcodes) {
            $normalized = \array_values(\array_unique(\array_merge(
                $normalized,
                \array_map(
                    static function ($shortcode) {
                        return \preg_replace('/[^a-z0-9-]/', '-', \strtolower(\trim($shortcode, ':(){}[]')));
                    },
                    (array) $shortcodes
                )
            )));
        }

        return \array_unique(\array_filter($normalized));
    }

    public function __toString(): string
    {
        return $this->getUnicode() ?: '';
    }

    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function getEmoticon(): ?string
    {
        return $this->emoticon;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function getGroup(): ?int
    {
        return $this->group;
    }

    public function getHexcode(): ?string
    {
        return $this->hexcode;
    }

    public function getHtmlEntity(): ?string
    {
        $hexcode = $this->getHexcode();

        return $hexcode ? '&#x' . \implode(';&#x', \explode('-', $hexcode)) . ';' : null;
    }

    public function getIterator(): \ArrayObject
    {
        return new \ArrayObject($this->toArray());
    }

    public function getOrder(): int
    {
        return $this->order;
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
     * @param string[]|null $exclude
     *
     * @return string[]
     */
    public function getShortcodes(?array $exclude = null): array
    {
        return $exclude ? \array_diff($this->shortcodes, $exclude) : $this->shortcodes;
    }

    public function getSkin(int $tone = EmojibaseSkinsInterface::LIGHT_SKIN): ?Emoji
    {
        return \current(
            $this->getSkins()->filter(
                static function (Emoji $emoji) use ($tone) {
                    return \in_array($tone, (array) $emoji->getTone(), true);
                }
            )->getArrayCopy()
        ) ?: null;
    }

    public function getSkins(): Dataset
    {
        return $this->skins;
    }

    public function getSubgroup(): ?int
    {
        return $this->subgroup;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @return int[]
     */
    public function getTone(): array
    {
        return $this->tone;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getUnicode(): ?string
    {
        $type = $this->getType();

        return $type === EmojibaseInterface::EMOJI && ($emoji = $this->getEmoji()) ? $emoji : $this->getText();
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint

    public function getVersion(): ?float
    {
        return $this->version;
    }

    /**
     * @param string $key
     */
    public function offsetExists($key): bool // phpcs:ignore
    {
        $method = \current(\array_filter(['get' . \ucfirst($key), $key], function ($method) {
            return \method_exists($this, $method);
        }));

        return ($method || \property_exists($this, $key)) && isset($this->$key);
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function offsetGet($key) // phpcs:ignore
    {
        // Use the dedicated "get" method for the property instead.
        $method = \current(\array_filter(['get' . \ucfirst($key), $key], function ($method) {
            return \method_exists($this, $method);
        }));

        // Otherwise, ensure the property actually exists.
        if (! $method && ! \property_exists($this, $key)) {
            throw new \RuntimeException(\sprintf('Unknown property: %s', $key));
        }

        return $method ? $this->$method() : $this->$key;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function offsetSet($key, $value): void // phpcs:ignore
    {
        // Immediately return if key or value isn't valid.
        if (! \property_exists($this, $key) || ! isset($value) || $value === '') {
            return;
        }

        $this->$key = self::normalizePropertyValue($key, $value);
    }

    /**
     * @param string $key
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function offsetUnset($key): void // phpcs:ignore
    {
        if (\property_exists($this, $key)) {
            $this->$key = null;
        }
    }

    // phpcs:enable

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $array = [];
        foreach (\get_object_vars($this) as $key => $value) {
            if (! isset($value)) {
                continue;
            }

            if ($value instanceof \Iterator) {
                $array[$key] = \iterator_to_array($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }
}
