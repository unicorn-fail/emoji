<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use League\Configuration\Exception\InvalidConfigurationException;
use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Dataset\Dataset;
use UnicornFail\Emoji\Dataset\RuntimeDataset;
use UnicornFail\Emoji\EmojiConverter;
use UnicornFail\Emoji\EmojiConverterInterface;
use UnicornFail\Emoji\Emojibase\EmojibaseDatasetInterface;
use UnicornFail\Emoji\Emojibase\EmojibaseShortcodeInterface;
use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Lexer\EmojiLexer;
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Parser\EmojiParserInterface;
use UnicornFail\Emoji\Renderer\DocumentRendererInterface;

class EmojiConverterTest extends TestCase
{
    public const ENCODINGS = [
        'en' => [
            'raw'       => '🙍🏿‍♂️ is leaving on an &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D',
            'html'      => '&#x1F64D;&#x1F3FF;&#x200D;&#x2642;&#xFE0F; is leaving on an &#x2708;️. Going to ' .
                '&#x1F1E6;&#x1F1FA;. Might see some &#x1F998;! &#x2764; Remember to &#x1F4F1; &#x1F600;',
            'shortcode' => ':man-frowning-tone5: is leaving on an :airplane:️. Going to :australia:. ' .
                'Might see some :kangaroo:! :heart: Remember to :android: :grinning:',
            'unicode'   => '🙍🏿‍♂️ is leaving on an ✈️️. Going to 🇦🇺. Might see some 🦘! ❤️ Remember to 📱 😀',
        ],
    ];

    /** @var EmojiConverter */
    protected $converter;

    /**
     * @return mixed[]
     */
    public function providerEncodings(): array
    {
        $data                  = [];
        $data['T_EMOTICON']    = [EmojiLexer::T_EMOTICON, self::ENCODINGS['en']['raw']];
        $data['T_HTML_ENTITY'] = [EmojiLexer::T_HTML_ENTITY, self::ENCODINGS['en']['html']];
        $data['T_SHORTCODE']   = [EmojiLexer::T_SHORTCODE, self::ENCODINGS['en']['shortcode']];
        $data['T_UNICODE']     = [EmojiLexer::T_UNICODE, self::ENCODINGS['en']['unicode']];

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function providerLocalPresets(): array
    {
        $locales = \array_merge(['en-US'], EmojibaseDatasetInterface::SUPPORTED_LOCALES);

        $data = [];
        foreach ($locales as $locale) {
            foreach (EmojibaseShortcodeInterface::PRESETS as $preset) {
                $originalLocale = $locale;
                $label          = \sprintf('%s:%s', $locale, $preset);

                if ($locale === 'en-US') {
                    $locale = 'en';
                }

                $native = false;
                if ($preset === EmojibaseShortcodeInterface::PRESET_CLDR_NATIVE) {
                    $native = null;
                }

                $exception = null;
                if (! \file_exists(\sprintf('%s/%s/%s.gz', Dataset::DIRECTORY, $locale, $preset))) {
                    $exception = LocalePresetException::class;
                }

                $data[$label] = [$locale, $preset, $exception, $originalLocale, $native];
            }
        }

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function providerPresentation(): array
    {
        $data              = [];
        $data['-default-'] = [[], ':smiling-face:', '☺️'];
        $data['AUTO']      = [['presentation' => EmojibaseDatasetInterface::AUTO], ':smiling-face:', '☺︎'];
        $data['EMOJI']     = [['presentation' => EmojibaseDatasetInterface::EMOJI], ':smiling-face:', '☺️'];
        $data['TEXT']      = [['presentation' => EmojibaseDatasetInterface::TEXT], ':smiling-face:', '☺︎'];

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function providerShortcodePresets(): array
    {
        $data = [];
        foreach (EmojibaseShortcodeInterface::PRESETS as $preset) {
            $file = \sprintf('%s/../fixtures/%s.md', __DIR__, $preset);
            if (\file_exists($file) && ($contents = \file_get_contents($file))) {
                $data[$preset] = [$preset, $contents];
            }
        }

        foreach (EmojibaseShortcodeInterface::PRESET_ALIASES as $alias => $preset) {
            $file = \sprintf('%s/../fixtures/%s.md', __DIR__, $preset);
            if (\file_exists($file) && ($contents = \file_get_contents($file))) {
                $data[$alias] = [$alias, $contents];
            }
        }

        return $data;
    }

    /**
     * @dataProvider providerEncodings
     */
    public function testConvert(int $tokenType, string $expected): void
    {
        $actual = $this->convertTo(EmojiConverterInterface::TYPES[$tokenType], self::ENCODINGS['en']['raw']);
        $this->assertEquals($expected, $actual);
    }

    public function testInvoke(): void
    {
        /** @var EmojiConverter $converter */
        $converter = EmojiConverter::create();
        $this->assertEquals(self::ENCODINGS['en']['unicode'], $converter(self::ENCODINGS['en']['raw']));
    }

    /**
     * @dataProvider providerLocalPresets
     *
     * @psalm-param ?class-string<\Throwable> $exception
     */
    public function testLocalePresets(string $locale, string $preset, ?string $exception = null, ?string $originalLocal = null, ?bool $native = null): void
    {
        if ($exception !== null) {
            $this->expectException($exception);
            $this->expectExceptionMessage(\sprintf(
                "Attempted to load the locale \"%s\" dataset. However, the following preset(s) were unable to be loaded:\n%s",
                $originalLocal ?? $locale,
                \sprintf('%s: The following file does not exist or is not readable: %s', $preset, \sprintf(
                    '%s/%s/%s.gz',
                    Dataset::DIRECTORY,
                    $locale,
                    $preset
                ))
            ));
        }

        $configuration = [
            'locale' => $originalLocal ?? $locale,
            'native' => $native,
            'preset' => $preset,
        ];
        $environment   = Environment::create($configuration);
        $converter     = new EmojiConverter($environment);

        $this->assertSame($environment, $converter->getEnvironment());
        $this->assertTrue($environment->getRuntimeDataset() instanceof RuntimeDataset);

        // Convert so the parser actually runs the various parser/lexer code related to the local preset.
        $converter->convert('Test');

        // The variable must be manually emptied after each assertion in order to avoid memory leaks between tests.
        $converter = null;
    }

    public function testUnknownPreset(): void
    {
        $environment = Environment::create([
            'preset' => [EmojibaseShortcodeInterface::PRESET_CLDR_NATIVE, 'foo'],
        ]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("The item 'preset › 1' expects to be 'cldr'|'cldr-native'|'emojibase'|'emojibase...'|'github'|'iamcal'|'joypixels'|'discord'|'slack', 'foo' given.");

        // Trigger initialize so the configuration is built and validated.
        $environment->getRuntimeDataset();
    }

    public function testCustomParserRenderer(): void
    {
        $input    = 'foo';
        $document = $this->createMock(Document::class);

        $parser = new class ($document) implements EmojiParserInterface {
            /** @var Document */
            private $document;

            public function __construct(Document $document)
            {
                $this->document = $document;
            }

            public function parse(string $input): Document
            {
                return $this->document;
            }
        };

        $renderer = new class ($input) implements DocumentRendererInterface {
            /** @var string */
            private $content;

            public function __construct(string $content)
            {
                $this->content = $content;
            }

            public function renderDocument(Document $document): string
            {
                return $this->content;
            }
        };

        $environment = new Environment();
        $converter   = new EmojiConverter($environment, $parser, $renderer);

        $this->assertSame($environment, $converter->getEnvironment());
        $this->assertSame($parser, $converter->getParser());
        $this->assertSame($renderer, $converter->getRenderer());

        $this->assertSame($document, $parser->parse($input));
        $this->assertSame($input, $renderer->renderDocument($document));
        $this->assertSame($input, (string) $converter->convert($input));
    }

    /** @param array<string, mixed> $config */
    protected function convert(string $input, array $config = []): string
    {
        $converter = EmojiConverter::create($config);

        return (string) $converter->convert($input);
    }

    /**
     * @param string|string[]      $type
     * @param array<string, mixed> $config
     */
    protected function convertTo($type, string $input, array $config = []): string
    {
        $config['convert'] = $type;

        return $this->convert($input, $config);
    }

    public function testExcludedShortcodes(): void
    {
        $this->assertEquals(':iphone:', $this->convertTo(EmojiConverter::SHORTCODE, '📱', [
            'exclude' => [
                'shortcodes' => ['mobile-phone', 'android'],
            ],
        ]));
    }

    /**
     * @dataProvider providerPresentation
     *
     * @param array<string, mixed> $configuration
     */
    public function testPresentation(array $configuration, string $raw, string $expected): void
    {
        $actual = $this->convert($raw, $configuration);
        $this->assertSame($expected, $actual);
    }

    public function testReadme(): void
    {
        $unicode = $this->convert('We <3 :unicorn: :D!');
        $this->assertSame('We ❤️ 🦄 😀!', $unicode);

        $html = $this->convertTo(EmojiConverter::HTML_ENTITY, 'We <3 :unicorn: :D!');
        $this->assertSame('We &#x2764; &#x1F984; &#x1F600;!', $html);

        $shortcode = $this->convertTo(EmojiConverter::SHORTCODE, 'We <3 :unicorn: :D!');
        $this->assertSame('We :heart: :unicorn: :grinning:!', $shortcode);
    }

    /**
     * @dataProvider providerShortcodePresets
     */
    public function testShortcodeToUnicodePresets(string $preset, string $contents): void
    {
        $expected = (string) \file_get_contents(__DIR__ . '/../fixtures/unicode.md');
        $actual   = $this->convert($contents, ['preset' => $preset]);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider providerShortcodePresets
     */
    public function testUnicodeToShortcodePresets(string $preset, string $expected): void
    {
        $contents = (string) \file_get_contents(__DIR__ . '/../fixtures/unicode.md');
        $actual   = $this->convertTo(EmojiConverter::SHORTCODE, $contents, ['preset' => $preset]);
        $this->assertEquals($expected, $actual);
    }
}
