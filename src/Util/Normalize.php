<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Util;

use UnicornFail\Emoji\Emoji;
use UnicornFail\Emoji\Emojibase\DatasetInterface;

/**
 * {@internal}
 */
final class Normalize
{
    public const TYPE_METHODS = [
        'array' => 'toArray',
        'bool' => 'toBoolean',
        'boolean' => 'toBoolean',
        'double' => 'toFloat',
        'float' => 'toFloat',
        'int' => 'toInteger',
        'integer' => 'toInteger',
        'object' => 'toObject',
        'string' => 'toString',
    ];

    public const TYPES = ['array', 'bool', 'boolean', 'double', 'float', 'int', 'integer', 'null', 'object', 'string'];

    /**
     * @param mixed   $emojis
     * @param Emoji[] $dataset
     *
     * @return Emoji[]
     */
    public static function dataset($emojis = [], string $index = 'hexcode', array &$dataset = []): array
    {
        foreach (static::emojis($emojis) as $emoji) {
            /** @var string[] $keys */
            $keys = \array_filter((array) $emoji->$index);
            foreach ($keys as $k) {
                if (isset($dataset[$k])) {
                    continue;
                }

                $dataset[$k] = $emoji;

                static::dataset($emoji->skins, $index, $dataset);
            }
        }

        return $dataset;
    }

    /**
     * @param mixed $emojis
     *
     * @return Emoji[]
     */
    public static function emojis($emojis = []): array
    {
        if ($emojis instanceof Emoji) {
            $emojis = [$emojis];
        } elseif ($emojis instanceof \Iterator) {
            $emojis = \iterator_to_array($emojis);
        } else {
            $emojis = (array) $emojis;
        }

        /** @var Emoji[] $normalized */
        $normalized = [];

        /** @var mixed|string[]|Emoji $emoji */
        foreach ($emojis as &$emoji) {
            if (\is_array($emoji)) {
                $emoji = new Emoji($emoji);
            }

            \assert($emoji instanceof Emoji);
            $normalized[] = $emoji;
        }

        return $normalized;
    }

    public static function locale(string $locale): string
    {
        // Immediately return if locale is an exact match.
        if (\in_array($locale, DatasetInterface::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        // Immediately return if this local has already been normalized.
        /** @var string[] $normalized */
        static $normalized = [];
        if (isset($normalized[$locale])) {
            return $normalized[$locale];
        }

        $original              = $locale;
        $normalized[$original] = '';

        // Otherwise, see if it just needs some TLC.
        $locale = \strtolower($locale);
        $locale = \preg_replace('/[^a-z]/', '-', $locale) ?? $locale;
        foreach ([$locale, \current(\explode('-', $locale, 2))] as $locale) {
            if (\in_array($locale, DatasetInterface::SUPPORTED_LOCALES, true)) {
                $normalized[$original] = $locale;
                break;
            }
        }

        return $normalized[$original];
    }

    /**
     * @param mixed[]  $properties
     * @param string[] $types
     *
     * @return mixed[]
     */
    public static function properties(array $properties, array $types): array
    {
        $properties += \array_fill_keys(\array_keys($types), null);

        /** @psalm-var string $value */
        foreach ($properties as $key => $value) {
            /** @psalm-var string $value */
            $value            = Property::cast($types[$key] ?? '?string', $value);
            $properties[$key] = $value;
        }

        return $properties;
    }

    /**
     * @param mixed $value
     */
    public static function setType(&$value, string $type): bool
    {
        // Immediately return if not a valid type.
        if (! isset(self::TYPE_METHODS[$type])) {
            $value = null;

            return false;
        }

        $method = self::TYPE_METHODS[$type];

        /** @psalm-var string $value */
        $value = static::$method($value);

        return true;
    }

    /**
     * @param string|string[] $shortcode
     *
     * @return string[]
     */
    public static function shortcodes($shortcode): array
    {
        $normalized = [];

        /** @var string|string[] $shortcodes */
        foreach (\func_get_args() as $shortcodes) {
            $normalized = \array_merge($normalized, \array_map(static function ($shortcode) {
                        return \preg_replace('/[^a-z0-9-]/', '-', \strtolower(\trim((string) $shortcode, ':(){}[]')));
            }, (array) $shortcodes));
        }

        return \array_values(\array_unique(\array_filter($normalized)));
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]
     */
    public static function toArray($value): array
    {
        return (array) ($value ?? []);
    }

    /**
     * @param mixed $value
     */
    public static function toBoolean($value): bool
    {
        return (bool) ($value ?? false);
    }

    /**
     * @param mixed $value
     */
    public static function toFloat($value): float
    {
        return (float) ($value ?? 0.0);
    }

    /**
     * @param mixed $value
     */
    public static function toInteger($value): int
    {
        return (int) ($value ?? 0);
    }

    /**
     * @param mixed $value
     */
    public static function toObject($value): object
    {
        return (object) ($value ?? new \stdClass());
    }

    /**
     * @param mixed $value
     */
    public static function toString($value): string
    {
        return \is_array($value) ? \implode($value) : (string) ($value ?? '');
    }
}
