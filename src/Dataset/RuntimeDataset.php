<?php

declare(strict_types=1);

namespace League\Emoji\Dataset;

use League\Configuration\ConfigurationInterface;
use League\Emoji\Emojibase\EmojibaseDatasetInterface;
use League\Emoji\Emojibase\EmojibaseShortcodeInterface;
use League\Emoji\Exception\FileNotFoundException;
use League\Emoji\Exception\LocalePresetException;
use League\Emoji\Exception\MalformedArchiveException;
use League\Emoji\Exception\UnarchiveException;
use League\Emoji\Parser\EmojiParser;

final class RuntimeDataset implements \ArrayAccess, \Countable, \SeekableIterator
{
    public const DEFAULT = 'en';

    /** @var ConfigurationInterface */
    private $config;

    /** @var ?Dataset */
    private $dataset;

    /** @var ?string */
    private $locale;

    /** @var ?bool */
    private $native;

    /** @var ?string[] */
    private $presets;

    public function __construct(ConfigurationInterface $configuration, ?Dataset $dataset = null)
    {
        $this->config  = $configuration;
        $this->dataset = $dataset;
    }

    /**
     * @param string[] $indices
     *
     * @return false|string
     */
    public static function archive(Dataset $dataset, array $indices = EmojiParser::INDICES)
    {
        foreach ($indices as $index) {
            $dataset->indexBy($index);
        }

        $serialize = \serialize($dataset);

        return \gzencode($serialize, 9);
    }

    public static function unarchive(string $filename): Dataset
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
            $dataset = \unserialize((string) $decoded, [
                'allowed_classes' => [Dataset::class, Emoji::class],
            ]);
        } catch (\Throwable $throwable) {
            throw new MalformedArchiveException($filename, $throwable);
        }

        if (! $dataset instanceof Dataset) {
            throw new MalformedArchiveException($filename);
        }

        return $dataset;
    }

    public function count(): int
    {
        return $this->getDataset()->count();
    }

    public function current(): ?Emoji
    {
        /** @var ?Emoji $current */
        $current = $this->getDataset()->current();

        return $current;
    }

    /**
     * @param callable(Emoji):bool $callback
     */
    public function filter(callable $callback): RuntimeDataset
    {
        return new self($this->config, $this->getDataset()->filter($callback));
    }

    public function getDataset(): Dataset
    {
        if ($this->dataset === null) {
            /** @var \Throwable[] $throwables */
            $throwables = [];
            $locale     = $this->getLocale();
            $presets    = $this->getPresets();

            $remaining = $presets;
            while (\count($remaining) > 0) {
                $preset = \array_shift($remaining);
                try {
                    $this->dataset = self::unarchive(\sprintf('%s/%s/%s.gz', Dataset::DIRECTORY, $locale, $preset));
                    break;
                } catch (\Throwable $throwable) {
                    $throwables[$preset] = $throwable;
                }
            }

            if ($this->dataset === null) {
                if ($this->config->data()->has('locale')) {
                    $locale = (string) $this->config->data()->get('locale');
                }

                throw new LocalePresetException($locale, $throwables);
            }
        }

        return $this->dataset;
    }

    public function getLocale(): string
    {
        if ($this->locale === null) {
            $this->locale = (string) ($this->config->get('locale') ?? self::DEFAULT);
        }

        return $this->locale;
    }

    /**
     * @return string[]
     */
    public function getPresets(): array
    {
        if ($this->presets === null) {
            /** @var string[] $presets */
            $presets = (array) $this->config->get('preset');

            // Prepend the native preset if local is requires it and enabled.
            if ($this->isNative()) {
                \array_unshift($presets, EmojibaseShortcodeInterface::PRESET_CLDR_NATIVE);
            } else {
                /** @var int|false $key */
                $key = \array_search(EmojibaseShortcodeInterface::PRESET_CLDR_NATIVE, $presets, true);

                // Only remove the CLDR native preset if it's not the only one provided.
                if ($key !== false && \count($presets) !== 1) {
                    \array_splice($presets, $key, 1);
                }
            }

            $this->presets = \array_filter(\array_values(\array_unique(\array_filter($presets))));
        }

        return $this->presets;
    }

    public function indexBy(string $index = 'hexcode'): RuntimeDataset
    {
        return new self($this->config, $this->getDataset()->indexBy($index));
    }

    public function isNative(): bool
    {
        if ($this->native === null) {
            $locale  = $this->getLocale();
            $default = \in_array($locale, EmojibaseDatasetInterface::NON_LATIN_LOCALES, true);

            /** @var ?bool $native */
            $native = $this->config->get('native');

            $this->native = $native === null
                ? $default
                : $native && $default;
        }

        return $this->native;
    }

    public function key(): string
    {
        return (string) $this->getDataset()->key();
    }

    public function next(): void
    {
        $this->getDataset()->next();
    }

    /** @param string $offset */
    public function offsetExists($offset): bool // phpcs:ignore
    {
        return $this->getDataset()->offsetExists($offset);
    }

    /** @param string $offset */
    public function offsetGet($offset): ?Emoji // phpcs:ignore
    {
        return $this->getDataset()->offsetGet($offset);
    }

    /**
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void // phpcs:ignore
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    /** @param string $offset */
    public function offsetUnset($offset): void // phpcs:ignore
    {
        throw new \BadMethodCallException('Unable to modify immutable object.');
    }

    public function rewind(): void
    {
        $this->getDataset()->rewind();
    }

    /** @param int $position */
    public function seek($position): void // phpcs:ignore
    {
        $this->getDataset()->seek($position);
    }

    public function valid(): bool
    {
        return $this->getDataset()->valid();
    }
}
