<?php

declare(strict_types=1);

namespace League\Emoji\Dataset;

use League\Emoji\Util\ImmutableArrayIterator;
use League\Emoji\Util\Normalize;

/**
 * @method Emoji|null current()
 * @method Emoji[] getArrayCopy()
 */
final class Dataset extends ImmutableArrayIterator implements \ArrayAccess, \Countable, \SeekableIterator, \Serializable
{
    public const DIRECTORY = __DIR__ . '/../../datasets';

    /** @var string */
    private $index;

    /** @var Dataset[] */
    private $indices = [];

    /**
     * @param mixed $emojis
     */
    public function __construct($emojis = [], string $index = 'hexcode')
    {
        $this->index = $index;
        $normalized  = Normalize::dataset($emojis, $index);
        parent::__construct($normalized, \ArrayIterator::ARRAY_AS_PROPS | \ArrayIterator::STD_PROP_LIST);
    }

    /**
     * @param callable(Emoji):bool $callback
     */
    public function filter(callable $callback): Dataset
    {
        /** @var \Iterator $iterator */
        $iterator = $this;
        $filter   = new \CallbackFilterIterator($iterator, $callback);

        return new self($filter);
    }

    public function indexBy(string $index = 'hexcode'): Dataset
    {
        if (! isset($this->indices[$index])) {
            $this->indices[$index] = new self($this, $index);
        }

        return $this->indices[$index];
    }

    /**
     * @param string $key
     */
    public function offsetGet($key): ?Emoji // phpcs:ignore
    {
        // Normalize shortcodes to match index.
        if (\strpos($this->index, 'shortcode') !== false) {
            $key = (string) \current(Normalize::shortcodes($key));
        }

        /** @var ?Emoji $emoji */
        $emoji = parent::offsetGet($key);

        return $emoji;
    }
}
