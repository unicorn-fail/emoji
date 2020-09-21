<?php

declare(strict_types=1);

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UnicornFail\Emoji\Environment;

use UnicornFail\Emoji\Dataset\Dataset;
use UnicornFail\Emoji\Dataset\DatasetInterface;
use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Extension\CoreExtension;
use UnicornFail\Emoji\Parser\Parser;
use UnicornFail\Emoji\Parser\ParserInterface;

final class Environment extends AbstractConfigurableEnvironment implements EmojiEnvironmentInterface
{
    /** @var ?DatasetInterface */
    private $dataset;

    /** @var ?ParserInterface */
    private $parser;

    /**
     * @param mixed[]|\Traversable $configuration
     *
     * @return static
     */
    public static function create(?iterable $configuration = null)
    {
        $environment = new static($configuration);

        foreach (static::defaultExtensions() as $extension) {
            $environment->addExtension($extension);
        }

        return $environment;
    }

    /**
     * {@inheritDoc}
     */
    protected static function defaultExtensions(): iterable
    {
        return [new CoreExtension()];
    }

    /**
     * @param string[] $presets
     */
    protected static function loadLocalePreset(string $locale = 'en', array $presets = ShortcodeInterface::DEFAULT_PRESETS): DatasetInterface
    {
        $throwables = [];
        $presets    = \array_filter($presets);
        $remaining  = $presets;
        while (\count($remaining) > 0) {
            $preset = \array_shift($remaining);
            try {
                return Dataset::unarchive(\sprintf('%s/%s/%s.gz', DatasetInterface::DIRECTORY, $locale, $preset));
            } catch (\Throwable $throwable) {
                $throwables[$preset] = $throwable;
            }
        }

        throw new LocalePresetException($locale, $throwables);
    }

    public function getDataset(): DatasetInterface
    {
        if ($this->dataset === null) {
            $locale = $this->getConfiguration()->get('locale');
            \assert(\is_string($locale));

            /** @var string[] $preset */
            $preset = $this->getConfiguration()->get('preset');

            $this->dataset = self::loadLocalePreset($locale, $preset);
        }

        return $this->dataset;
    }

    public function getParser(): ParserInterface
    {
        if ($this->parser === null) {
            $this->parser = new Parser($this);
        }

        return $this->parser;
    }

    public function setDataset(DatasetInterface $dataset): void
    {
        $this->dataset = $dataset;
    }

    public function setParser(ParserInterface $parser): void
    {
        $this->parser = $parser;
    }
}
