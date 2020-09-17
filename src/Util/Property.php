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
     * @return mixed[]
     */
    protected function castIterable($value): array
    {
        $value = (array) $value;
        $type  = $this->nullable ? \sprintf('?%s', $this->type) : $this->type;

        /** @var string[] $types */
        $types = \array_fill_keys(\array_keys($value), $type);

        return Normalize::properties($value, $types);
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

        $value = $this->castValueCallback($value);

        if ($this->iterable) {
            return $this->castIterable($value);
        }

        return $this->castValueType($value);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function castValueCallback($value)
    {
        if (($callback = $this->callback) && \is_callable($callback)) {
            $value = $callback($value);
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function castValueType($value)
    {
        if (($type = $this->customType) && \class_exists($type)) {
            $value = new $type($value);
        } else {
            Normalize::setType($value, $this->type);
        }

        return $this->emptyNullable && ! $value ? null : $value;
    }
}
