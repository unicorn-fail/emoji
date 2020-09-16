<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

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
            $properties[$key] = static::propertyValue($types, $key, $value);
        }

        return $properties;
    }

    /**
     * @param string[]   $types
     * @param int|string $key
     * @param mixed      $value
     *
     * @return mixed
     */
    protected static function propertyValue(array $types, $key, $value)
    {
        $type = $types[$key] ?? '?string';

        $emptyNullable = false;
        $nullable      = false;
        $iterable      = false;

        // Determine if type is empty or nullable, prefixed with ?!.
        if (\substr($type, 0, 2) === '!?') {
            $emptyNullable = true;
            $nullable      = true;
            $type          = \substr($type, 2);
        // Determine if type is nullable, prefixed with ?.
        } elseif ($type[0] === '?') {
            $nullable = true;
            $type     = \substr($type, 1);
        }

        // Immediately return if already empty or null.
        if (($emptyNullable && ! $value) || ($nullable && $value === null)) {
            return null;
        }

        // Determine if type is iterable, suffixed with [].
        if (\substr($type, -2, 2) === '[]') {
            $iterable = true;
            $type     = \substr($type, 0, -2);
        }

        // Determine if PHP type.
        $phpType = \in_array($type, self::TYPES, true);

        if (! $phpType && \class_exists($type)) {
            $value = new $type($value);
            if ($iterable && \is_iterable($value)) {
                return $value;
            }
        }

        if (! $phpType && \is_callable($type)) {
            $value = $type($value);
            if ($iterable && \is_iterable($value)) {
                return $value;
            }
        }

        if ($iterable) {
            $value = (array) $value;
            $type  = $nullable ? \sprintf('?%s', $type) : $type;

            /** @var string[] $types */
            $types = \array_fill_keys(\array_keys($value), $type);

            return static::properties($value, $types);
        }

        return $phpType ? static::setType($value, $type, $emptyNullable) : $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]|bool|float|int|object|string|null
     */
    public static function setType($value, string $type, bool $emptyNullable = false)
    {
        if ($emptyNullable && ! $value) {
            return null;
        }

        switch ($type) {
            case 'array':
                $value = (array) ($value ?? []);
                break;

            case 'bool':
            case 'boolean':
                $value = (bool) ($value ?? false);
                break;

            case 'double':
            case 'float':
                $value = (float) ($value ?? 0.0);
                break;

            case 'int':
            case 'integer':
                $value = (int) ($value ?? 0);
                break;

            case 'null':
                $value = null;
                break;

            case 'object':
                $value = (object) ($value ?? new \stdClass());
                break;

            case 'string':
                $value = (string) ($value ?? '');
                break;
        }

        return $emptyNullable && ! $value ? null : $value;
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
