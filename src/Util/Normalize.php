<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Util;

/**
 * {@internal}
 */
final class Normalize
{
    public const TYPES = ['array', 'bool', 'boolean', 'double', 'float', 'int', 'integer', 'null', 'object', 'string'];

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
}
