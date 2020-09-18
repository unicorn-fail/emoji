<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use Dflydev\DotAccessData\Data;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnicornFail\Emoji\Emojibase\DatasetInterface;
use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\InvalidConfigurationException;
use UnicornFail\Emoji\Util\Normalize;

class Configuration extends Data implements ConfigurationInterface
{
    /**
     * @param mixed[]|\Traversable $configuration
     */
    public function __construct(?iterable $configuration = null)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $configuration !== null ? (new \ArrayObject($configuration))->getArrayCopy() : [];

        try {
            $data = $resolver->resolve($options);
        } catch (\Throwable $throwable) {
            throw new InvalidConfigurationException($throwable->getMessage(), (int) $throwable->getCode(), $throwable->getPrevious());
        }

        parent::__construct($data);
    }

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public static function create(?iterable $configuration = null): ConfigurationInterface
    {
        if ($configuration instanceof ConfigurationInterface) {
            return $configuration;
        }

        return new self($configuration);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->defineConvertEmoticons($resolver);
        $this->defineExcludeShortcodes($resolver);
        $this->defineLocale($resolver);
        $this->defineNative($resolver);
        $this->definePresentation($resolver);
        $this->definePreset($resolver);
        $this->defineStringableType($resolver);
    }

    protected function defineConvertEmoticons(OptionsResolver $resolver): void
    {
        $resolver->define('convertEmoticons')
            ->allowedTypes('bool')
            ->default(true);
    }

    protected function defineExcludeShortcodes(OptionsResolver $resolver): void
    {
        $resolver->define('excludeShortcodes')
            ->allowedTypes('string', 'string[]')
            ->default([])
            ->normalize(
            /**
             * @param string|string[] $value
             *
             * @return string[]
             */
                static function (Options $options, $value): array {
                    return Normalize::shortcodes($value);
                }
            );
    }

    protected function defineLocale(OptionsResolver $resolver): void
    {
        $resolver->define('locale')
            ->allowedTypes('string')
            ->allowedValues(static function (string $value): bool {
                return ! ! Normalize::locale($value);
            })
            ->default('en')
            ->normalize(static function (Options $options, string $value): string {
                return Normalize::locale($value);
            });
    }

    protected function defineNative(OptionsResolver $resolver): void
    {
        $resolver->define('native')
            ->allowedTypes('bool')
            ->default(static function (Options $options): bool {
                return \in_array($options['locale'], DatasetInterface::NON_LATIN_LOCALES, true);
            })
            ->normalize(static function (Options $options, bool $value) {
                return $value && \in_array($options['locale'], DatasetInterface::NON_LATIN_LOCALES, true);
            });
    }

    protected function definePresentation(OptionsResolver $resolver): void
    {
        $resolver->define('presentation')
            ->allowedTypes('int', 'null')
            ->allowedValues(...DatasetInterface::SUPPORTED_PRESENTATIONS)
            ->default(DatasetInterface::EMOJI);
    }

    protected function definePreset(OptionsResolver $resolver): void
    {
        $resolver->define('preset')
            ->allowedTypes('string', 'string[]')
            ->allowedValues(\Closure::fromCallable([$this, 'definePresetAllowedValues']))
            ->default(ShortcodeInterface::DEFAULT_PRESETS)
            ->normalize(\Closure::fromCallable([$this, 'definePresetNormalize']));
    }

    /**
     * @param mixed $values
     */
    protected function definePresetAllowedValues($values): bool
    {
        foreach ((array) $values as $value) {
            \assert(\is_string($value));
            if (! \in_array($value, ShortcodeInterface::SUPPORTED_PRESETS, true)) {
                throw new InvalidOptionsException(\sprintf(
                    'The option "preset" with value "%s" is invalid. Accepted values are: %s.',
                    $value,
                    \implode(', ', \array_map(static function ($s) {
                        return \sprintf('"%s"', $s);
                    }, ShortcodeInterface::SUPPORTED_PRESETS))
                ));
            }
        }

        return true;
    }

    /**
     * @param mixed $value
     *
     * @return string[]
     */
    protected function definePresetNormalize(Options $options, $value): array
    {
        // Presets.
        $presets = [];
        foreach ((array) $value as $preset) {
            \assert(\is_string($preset));
            if (isset(ShortcodeInterface::PRESET_ALIASES[$preset])) {
                $presets[] = ShortcodeInterface::PRESET_ALIASES[$preset];
            } elseif (isset(ShortcodeInterface::PRESETS[$preset])) {
                $presets[] = ShortcodeInterface::PRESETS[$preset];
            }
        }

        // Prepend the native preset if local is requires it and enabled.
        if ($options['native']) {
            \array_unshift($presets, ShortcodeInterface::PRESET_CLDR_NATIVE);
        }

        return \array_values(\array_unique($presets));
    }

    protected function defineStringableType(OptionsResolver $resolver): void
    {
        $resolver->define('stringableType')
            ->allowedTypes('int')
            ->allowedValues(Lexer::T_EMOTICON, Lexer::T_HTML_ENTITY, Lexer::T_SHORTCODE, Lexer::T_UNICODE)
            ->default(Lexer::T_UNICODE);
    }

    public function getIterator(): \ArrayObject
    {
        return new \ArrayObject($this->export());
    }
}
