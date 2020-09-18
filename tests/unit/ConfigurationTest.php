<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Configuration;
use UnicornFail\Emoji\Emojibase\ShortcodeInterface;
use UnicornFail\Emoji\Exception\InvalidConfigurationException;

class ConfigurationTest extends TestCase
{
    /**
     * @param mixed        $value
     * @param mixed[]|null $allowedValues
     */
    protected function createInvalidOptionException(string $name, $value, ?array $allowedValues = null): InvalidConfigurationException
    {
        $message = \sprintf('The option "%s" with value %s is invalid.', $name, $this->formatValue($value));
        if (isset($allowedValues) && \count($allowedValues) > 0) {
            $message .= \sprintf(' Accepted values are: %s.', $this->formatValues($allowedValues));
        }

        return new InvalidConfigurationException($message);
    }

    /**
     * @param mixed        $value
     * @param mixed[]|null $allowedValues
     */
    protected function expectInvalidOptionException(string $name, $value, ?array $allowedValues = null): void
    {
        $this->expectExceptionObject($this->createInvalidOptionException($name, $value, $allowedValues));
    }

    /**
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        if (\is_object($value)) {
            return \get_class($value);
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_string($value)) {
            return '"' . $value . '"';
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if ($value === null) {
            return 'null';
        }

        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        return (string) $value;
    }

    /**
     * @param mixed[] $values
     */
    private function formatValues(array $values): string
    {
        foreach ($values as $key => $value) {
            $values[$key] = $this->formatValue($value);
        }

        return \implode(', ', $values);
    }

    /**
     * @param mixed $value
     */
    protected function datasetLabel(string $name, $value): string
    {
        if (\is_array($value)) {
            return \sprintf('%s: [%s]', $name, $this->formatValues($value));
        }

        return \sprintf('%s: %s', $name, $this->formatValue($value));
    }

    /**
     * @return mixed[]
     */
    public function providerConfiguration(): array
    {
        $data = [];

        // Valid locales.
        foreach (['en', 'EN', 'en-US', 'en_US', 'EN_us', 'EN-US'] as $value) {
            $name     = 'locale';
            $label    = $this->datasetLabel($name, $value);
            $config   = [
                $name => $value,
                'native' => true,
            ];
            $expected = [
                $name => 'en',
                'native' => false,
            ];

            $data[$label] = [$config, $expected];
        }

        // Invalid locales.
        foreach (['100-134', 'foo_bar', 'english'] as $value) {
            $name      = 'locale';
            $label     = $this->datasetLabel($name, $value);
            $config    = [
                $name => $value,
                'native' => true,
            ];
            $expected  = [];
            $exception = $this->createInvalidOptionException($name, $value);

            $data[$label] = [$config, $expected, $exception];
        }

        $excludeShortcodes = [
            [':', []],
            ['foo bar', 'foo-bar'],
            ['foo:bar', 'foo-bar'],
            [':foo-bar:', 'foo-bar'],
            ['(FOO bar)', 'foo-bar'],
            ['[foo:BAR]', 'foo-bar'],
            ['{foo bar}', 'foo-bar'],
            ['Foo_Bar', 'foo-bar'],
            [['foo bar', 'foo:bar'], 'foo-bar'],
        ];
        foreach ($excludeShortcodes as $excludeShortcode) {
            [$raw, $expected] = $excludeShortcode;
            $name             = 'exclude.shortcodes';
            $label            = $this->datasetLabel($name, $raw);

            $data[$label] = [
                [$name => $raw],
                [$name => (array) $expected],
            ];
        }

        // Valid presets.
        foreach (ShortcodeInterface::SUPPORTED_PRESETS as $value) {
            $name   = 'preset';
            $label  = $this->datasetLabel($name, $value);
            $native = \strpos($value, 'native') !== false;
            $config = [
                $name => $value,
            ];
            if ($native) {
                $config['locale'] = 'ko';
            }

            $expected = [
                $name => \array_values(\array_unique(\array_filter([
                    $native ? ShortcodeInterface::PRESET_CLDR_NATIVE : null,
                    ShortcodeInterface::PRESET_ALIASES[$value] ?? $value,
                ]))),
            ];

            $data[$label] = [$config, $expected];
        }

        // Invalid presets.
        foreach (['100-134', 'foo_bar', 'english'] as $value) {
            $name      = 'preset';
            $label     = $this->datasetLabel($name, $value);
            $config    = [$name => $value];
            $expected  = [];
            $exception = $this->createInvalidOptionException($name, $value, ShortcodeInterface::SUPPORTED_PRESETS);

            $data[$label] = [$config, $expected, $exception];
        }

        return $data;
    }

    public function testCreate(): void
    {
        $configuration = new Configuration();
        $this->assertSame($configuration, Configuration::create($configuration));

        $data          = ['exclude.shortcodes' => ['foo-bar']];
        $configuration = new Configuration(new \ArrayObject($data));
        $actual        = $configuration->export();

        $this->assertArrayHasKey('exclude', $actual);
        $this->assertArrayHasKey('shortcodes', $actual['exclude']);
        $this->assertEmpty(\array_diff($data['exclude.shortcodes'], $actual['exclude']['shortcodes']));
    }

    /**
     * @dataProvider providerConfiguration
     *
     * @param mixed[] $data
     * @param mixed[] $expected
     */
    public function testConfiguration(array $data, array $expected, ?InvalidConfigurationException $exception = null): void
    {
        if ($exception) {
            $this->expectExceptionObject($exception);
        }

        $configuration = Configuration::create($data);
        foreach ($configuration as $name => $value) {
            if (! isset($expected[$name])) {
                continue;
            }

            $this->assertSame($expected[$name], $value);
        }

        foreach ($expected as $name => $value) {
            $this->assertSame($value, $configuration->get($name));
        }
    }
}
