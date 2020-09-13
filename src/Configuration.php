<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use Dflydev\DotAccessData\Data;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnicornFail\Emoji\Exception\InvalidConfigurationException;

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

    protected static function normalizeLocale(string $locale): string
    {
        // Immediately return if locale is an exact match.
        if (\in_array($locale, EmojibaseInterface::SUPPORTED_LOCALES, true)) {
            return $locale;
        }

        // Immediately return if this local has already been normalized.
        static $normalized = [];
        if (isset($normalized[$locale])) {
            return $normalized[$locale];
        }

        $original              = $locale;
        $normalized[$original] = '';

        // Otherwise, see if it just needs some TLC.
        $locale = \strtolower($locale);
        $locale = \preg_replace('/[^a-z]/', '-', $locale) ?? $locale;
        foreach ([$locale, \current(\explode('-', $locale, 2))] as $locale) {
            if (\in_array($locale, EmojibaseInterface::SUPPORTED_LOCALES, true)) {
                $normalized[$original] = $locale;
                break;
            }
        }

        return $normalized[$original];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('convertEmoticons')
            ->allowedTypes('bool')
            ->default(true);

        $resolver->define('excludeShortcodes')
            ->allowedTypes('string', 'string[]')
            ->default([])
            ->normalize(
                /**
                 * @param mixed $value
                 *
                 * @return string[]
                 */
                static function (Options $options, $value): array {
                    if (! $value) {
                        return $value;
                    }

                    return Emoji::normalizeShortcodes($value);
                }
            );

        $resolver->define('locale')
            ->allowedTypes('string')
            ->allowedValues(static function (string $value): bool {
                return ! ! static::normalizeLocale($value);
            })
            ->default('en')
            ->normalize(static function (Options $options, string $value): string {
                return static::normalizeLocale($value);
            });

        $resolver->define('native')
            ->allowedTypes('bool')
            ->default(static function (Options $options): bool {
                return \in_array($options['locale'], EmojibaseInterface::NON_LATIN_LOCALES, true);
            })
            ->normalize(static function (Options $options, bool $value) {
                return $value && \in_array($options['locale'], EmojibaseInterface::NON_LATIN_LOCALES, true);
            });

        $resolver->define('presentation')
            ->allowedTypes('int', 'null')
            ->allowedValues(...EmojibaseInterface::SUPPORTED_PRESENTATIONS)
            ->default(EmojibaseInterface::EMOJI);

        $resolver->define('preset')
            ->allowedTypes('string', 'string[]')
            ->allowedValues(
                /**
                 * @param mixed $value
                 */
                static function ($value): bool {
                    foreach ((array) $value as $v) {
                        if (! \in_array($v, EmojibaseShortcodeInterface::SUPPORTED_PRESETS, true)) {
                            throw new InvalidOptionsException(\sprintf(
                                'The option "preset" with value "%s" is invalid. Accepted values are: %s.',
                                $v,
                                \implode(', ', \array_map(static function ($s) {
                                    return \sprintf('"%s"', $s);
                                }, EmojibaseShortcodeInterface::SUPPORTED_PRESETS))
                            ));
                        }
                    }

                    return true;
                }
            )
            ->default(EmojibaseShortcodeInterface::DEFAULT_PRESETS)
            ->normalize(
                /**
                 * @param mixed $value
                 *
                 * @return string[]
                 */
                static function (Options $options, $value): array {
                    // Presets.
                    $presets = [];
                    foreach ((array) $value as $preset) {
                        if (isset(EmojibaseShortcodeInterface::PRESET_ALIASES[$preset])) {
                            $presets[] = EmojibaseShortcodeInterface::PRESET_ALIASES[$preset];
                        } elseif (isset(EmojibaseShortcodeInterface::PRESETS[$preset])) {
                            $presets[] = EmojibaseShortcodeInterface::PRESETS[$preset];
                        }
                    }

                    // Prepend the native preset if local is requires it and enabled.
                    if ($options['native']) {
                        \array_unshift($presets, EmojibaseShortcodeInterface::PRESET_CLDR_NATIVE);
                    }

                    return \array_values(\array_unique($presets));
                }
            );

        $resolver->define('stringableType')
            ->allowedTypes('int')
            ->allowedValues(Parser::T_EMOTICON, Parser::T_HTML_ENTITY, Parser::T_SHORTCODE, Parser::T_UNICODE)
            ->default(Parser::T_UNICODE);
    }

    public function getIterator(): \ArrayObject
    {
        return new \ArrayObject($this->export());
    }
}
