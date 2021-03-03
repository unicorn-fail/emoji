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

use Dflydev\DotAccessData\Data;
use League\Configuration\Configuration;
use League\Configuration\ConfigurationBuilderInterface;
use League\Configuration\Exception\InvalidConfigurationException;
use Nette\Schema\Expect;
use UnicornFail\Emoji\Dataset\Dataset;
use UnicornFail\Emoji\Dataset\DatasetInterface;
use UnicornFail\Emoji\Emojibase\DatasetInterface as EmojibaseDatasetInterface;
use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Extension\CoreExtension;
use UnicornFail\Emoji\Parser\Lexer;
use UnicornFail\Emoji\Parser\Parser;
use UnicornFail\Emoji\Parser\ParserInterface;
use UnicornFail\Emoji\Util\Normalize;

final class Environment extends AbstractConfigurableEnvironment implements EmojiEnvironmentInterface
{
    /** @var ?DatasetInterface */
    private $dataset;

    /** @var ?ParserInterface */
    private $parser;

    /**
     * @param array<string, mixed> $configuration
     */
    public static function create(array $configuration = []): self
    {
        $environment = new self($configuration);

        foreach (self::defaultExtensions() as $extension) {
            $environment->addExtension($extension);
        }

        return $environment;
    }

    public static function createDefaultConfiguration(): ConfigurationBuilderInterface
    {
        $config = new Configuration();

        // @todo Figure out a better way to provide context to the normalizing (before) callbacks.
        $refConfig     = new \ReflectionObject($config);
        $refUserConfig = $refConfig->getProperty('userConfig');
        $refUserConfig->setAccessible(true);

        /** @var Data $userConfig */
        $userConfig = $refUserConfig->getValue($config);

        $config->addSchema(
            'convertEmoticons',
            Expect::bool(true)
        );

        $config->addSchema(
            'exclude',
            Expect::structure([
                'shortcodes' => Expect::arrayOf('string')
                    ->default([])
                    ->before(
                    /**
                     * @param string|string[] $value
                     *
                     * @return string[]
                     */
                        static function ($value): array {
                            return Normalize::shortcodes($value);
                        }
                    ),
            ])
        );

        $config->addSchema(
            'locale',
            Expect::anyOf(...EmojibaseDatasetInterface::SUPPORTED_LOCALES)
                ->default('en')
                ->before(
                    static function (string $value): string {
                        return Normalize::locale($value);
                    }
                )
        );

        $config->addSchema(
            'native',
            Expect::bool()
                ->before(
                    static function (?bool $value = null) use ($userConfig): bool {
                        /** @var string $locale */
                        $locale = $userConfig->has('locale')
                            ? $userConfig->get('locale')
                            : 'en';

                        $default = \in_array($locale, EmojibaseDatasetInterface::NON_LATIN_LOCALES, true);

                        if ($value === null) {
                            return $default;
                        }

                        $native = $value && $default;

                        $userConfig->set('native', $native);

                        return $native;
                    }
                )
        );

        $config->addSchema(
            'presentation',
            Expect::anyOf(...EmojibaseDatasetInterface::SUPPORTED_PRESENTATIONS)
                ->default(EmojibaseDatasetInterface::EMOJI)
        );

        $config->addSchema(
            'preset',
            Expect::arrayOf('string')
                ->default(ShortcodeInterface::DEFAULT_PRESETS)
                ->mergeDefaults(false)
                ->before(
                /**
                 * @param string|string[] $value
                 *
                 * @return string[]
                 */
                    static function ($value): array {
                        // Presets.
                        $presets = [];
                        foreach ((array) $value as $preset) {
                            if (isset(ShortcodeInterface::PRESET_ALIASES[$preset])) {
                                $presets[] = ShortcodeInterface::PRESET_ALIASES[$preset];
                            } elseif (isset(ShortcodeInterface::PRESETS[$preset])) {
                                $presets[] = ShortcodeInterface::PRESETS[$preset];
                            } else {
                                throw InvalidConfigurationException::forConfigOption(
                                    'preset',
                                    $preset,
                                    \sprintf(
                                        'Accepted values are: %s.',
                                        \implode(
                                            ', ',
                                            \array_map(
                                                static function ($s) {
                                                    return \sprintf('"%s"', $s);
                                                },
                                                ShortcodeInterface::SUPPORTED_PRESETS
                                            )
                                        )
                                    )
                                );
                            }
                        }

                        return \array_values(\array_unique($presets));
                    }
                )
        );

        $config->addSchema(
            'stringableType',
            Expect::anyOf(Lexer::EMOTICON, Lexer::HTML_ENTITY, Lexer::SHORTCODE, Lexer::UNICODE)
                ->default(Lexer::UNICODE)
        );

        return $config;
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
    protected static function loadLocalePreset(
        string $locale = 'en',
        array $presets = ShortcodeInterface::DEFAULT_PRESETS
    ): DatasetInterface {
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
            $config = $this->getConfiguration();
            $locale = $config->get('locale');
            \assert(\is_string($locale));

            /** @var string[] $presets */
            $presets = $config->get('preset');

            // Prepend the native preset if local is requires it and enabled.
            if ($config->get('native')) {
                \array_unshift($presets, ShortcodeInterface::PRESET_CLDR_NATIVE);
            }

            $this->dataset = self::loadLocalePreset($locale, $presets);
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
