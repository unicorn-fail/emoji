<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Dataset;

use UnicornFail\Emoji\Exception\FileNotFoundException;
use UnicornFail\Emoji\Exception\MalformedArchiveException;
use UnicornFail\Emoji\Exception\UnarchiveException;
use UnicornFail\Emoji\Parser\Parser;
use UnicornFail\Emoji\Util\ImmutableArrayIterator;
use UnicornFail\Emoji\Util\Normalize;

final class Dataset extends ImmutableArrayIterator implements DatasetInterface
{
    /** @var string */
    private $index;

    /** @var Dataset[] */
    private $indices = [];

    /**
     * @param mixed|mixed[] $emojis
     */
    public function __construct($emojis = [], string $index = 'hexcode')
    {
        $this->index = $index;
        parent::__construct(Normalize::dataset($emojis, $index), \ArrayIterator::ARRAY_AS_PROPS | \ArrayIterator::STD_PROP_LIST);
    }

    public static function unarchive(string $filename): self
    {
        if (! \file_exists($filename)) {
            throw new FileNotFoundException($filename);
        }

        if (
            ! ($contents = \file_get_contents($filename)) ||
            ! ($decoded = \gzdecode($contents))
        ) {
            throw new UnarchiveException($filename);
        }

        try {
            /** @var ?Dataset $dataset */
            $dataset = \unserialize((string) $decoded);
        } catch (\Throwable $throwable) {
            throw new MalformedArchiveException($filename, $throwable);
        }

        if (! $dataset instanceof Dataset) {
            throw new MalformedArchiveException($filename);
        }

        return $dataset;
    }

    /**
     * @param string[] $indices
     *
     * @return false|string
     */
    public function archive(array $indices = Parser::INDICES)
    {
        foreach ($indices as $index) {
            $this->indexBy($index);
        }

        $serialize = \serialize($this);

        return \gzencode($serialize, 9);
    }

    /**
     * @param callable(Emoji):bool $callback
     */
    public function filter(callable $callback): Dataset
    {
        /** @var \Iterator $this */
        $iterator = new \CallbackFilterIterator($this, $callback);

        return new self($iterator);
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
            $key = \current(Normalize::shortcodes($key));
        }

        if (! $key) {
            return null;
        }

        /** @var ?Emoji $emoji */
        $emoji = parent::offsetGet($key);

        return $emoji;
    }
}
