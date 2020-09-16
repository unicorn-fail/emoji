<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Util;

class Property
{
    public const REGEX = '/^(?:(?P<emptyNullable>[!?]{2})|(?P<nullable>[!?]))??(?P<customType>[^[<]+)?(?P<type>array|bool|boolean|double|float|int|integer|null|object|string)??(?P<iterable>\[\])?(?:<(?P<callback>[^[]+)>)??$/Ui';

    public const TYPES = ['array', 'bool', 'boolean', 'double', 'float', 'int', 'integer', 'null', 'object', 'string'];

    /** @var ?string */
    public $callback;

    /** @var ?string */
    public $customType;

    /** @var bool */
    public $emptyNullable;

    /** @var bool */
    public $nullable;

    /** @var bool */
    public $iterable;

    /** @var bool */
    public $isPhpType;

    /** @var string */
    public $type;

    public function __construct(string $type)
    {
        \preg_match(self::REGEX, $type, $matches, PREG_UNMATCHED_AS_NULL);
        $this->callback      = $matches['callback'] ?? null;
        $this->customType    = $matches['customType'] ?? null;
        $this->emptyNullable = ! ! ($matches['emptyNullable'] ?? false);
        $this->iterable      = ! ! ($matches['iterable'] ?? false);
        $this->nullable      = ! ! ($this->emptyNullable || ($matches['nullable'] ?? false));
        $this->type          = $matches['type'] ?? 'mixed';
        $this->isPhpType     = \in_array($this->type, self::TYPES, true);
    }

    /**
     * @param mixed $value
     *
     * @return bool|float|int|mixed[]|object|string|null
     */
    public static function cast(string $type, $value)
    {
        return (new self($type))->castValue($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function castCallback($value)
    {
        $callback = $this->callback;
        if (\is_callable($callback)) {
            $value = $callback($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function castCustomType($value)
    {
        $type = $this->customType;
        if (\is_string($type) && \class_exists($type)) {
            $value = new $type($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]|bool|float|int|object|string|null
     */
    protected function castPhpType($value)
    {
        switch ($this->type) {
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

        return $this->emptyNullable && ! $value ? null : $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed[]|bool|float|int|object|string|null
     */
    public function castValue($value)
    {
        // Immediately return if nullable or empty.
        if (($this->nullable && $value === null) || ($this->emptyNullable && ! $value)) {
            return null;
        }

        if ($this->callback) {
            $value = $this->castCallback($value);
        }

        if ($this->iterable) {
            $value = (array) $value;
            $type  = $this->nullable ? \sprintf('?%s', $this->type) : $this->type;

            /** @var string[] $types */
            $types = \array_fill_keys(\array_keys($value), $type);

            return Normalize::properties($value, $types);
        }

        if ($this->customType) {
            $value = $this->castCustomType($value);
        } else {
            $value = $this->castPhpType($value);
        }

        return $value;
    }
}
