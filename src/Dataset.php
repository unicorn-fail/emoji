<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Exception\FileNotFoundException;
use UnicornFail\Emoji\Exception\MalformedArchiveException;
use UnicornFail\Emoji\Exception\UnarchiveException;
use UnicornFail\Emoji\Util\ImmutableArrayIterator;
use UnicornFail\Emoji\Util\Normalize;

final class Dataset extends ImmutableArrayIterator
{
    public const DIRECTORY = __DIR__ . '/../datasets';

    /** @var array<string, Dataset> */
    protected $indices = [];

    /**
     * @param mixed $emojis
     */
    public function __construct($emojis = [], string $index = 'hexcode')
    {
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
            $dataset = \unserialize($decoded);
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

    public function filter(callable $callback): Dataset
    {
        return new self(new \CallbackFilterIterator($this, $callback));
    }

    public function indexBy(string $index = 'hexcode'): Dataset
    {
        if (! isset($this->indices[$index])) {
            $this->indices[$index] = new self($this, $index);
        }

        return $this->indices[$index];
    }
}
