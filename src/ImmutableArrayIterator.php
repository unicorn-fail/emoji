<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

class ImmutableArrayIterator extends \ArrayIterator
{
    /**
     * @return mixed
     */
    public function __get(string $key)
    {
        $method = 'get' . \ucfirst(\substr($key, 0, 3) === 'get' ? \substr($key, 3) : $key);
        if (! \method_exists($this, $method)) {
            $method = null;
        }

        return $method ? $this->$method() : $this->offsetGet($key);
    }

    public function __isset(string $key): bool
    {
        return $this->__get($key) !== null;
    }

    /**
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    public function __unset(string $key): void
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    /**
     * @param mixed $value
     */
    final public function append($value): void
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function offsetGet($key) // phpcs:ignore
    {
        return $this->offsetExists($key) ? parent::offsetGet($key) : null;
    }

    /**
     * @param string $offset
     * @param mixed  $value
     */
    final public function offsetSet($offset, $value): void // phpcs:ignore
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    /**
     * @param string $offset
     */
    final public function offsetUnset($offset): void // phpcs:ignore
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    /**
     * @param mixed $flags
     */
    final public function setFlags($flags): void // phpcs:ignore
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }
}
