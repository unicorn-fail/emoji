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

        foreach ($emojis as &$emoji) {
            if (\is_array($emoji)) {
                $emoji = new Emoji($emoji);
            }

            if (! $emoji instanceof Emoji) {
                throw new \RuntimeException(\sprintf('Passed array item must be an instance of %s.', Emoji::class));
            }
        }

        return (array) $emojis;
    }

    public static function locale(string $locale): string
    {
        // Immediately return if locale is an exact match.
        if (\in_array($locale, DatasetInterface::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        // Immediately return if this local has already been normalized.
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
        foreach ($properties as $key => $value) {
            $type             = $types[$key] ?? '?string';
            $properties[$key] = Property::cast($type, $value);
        }

        return $properties;
    }

    /**
     * @param mixed $value
     */
    public static function setType(&$value, string $type): bool
    {
        static $methods = [
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

        // Immediately return if not a valid type.
        if (! isset($methods[$type])) {
            $value = null;

            return false;
        }

        $method = $methods[$type];
        $value  = static::$method($value);

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