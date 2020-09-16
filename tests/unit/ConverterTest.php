<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPStan\Testing\TestCase;
use UnicornFail\Emoji\Converter;
use UnicornFail\Emoji\Dataset;
use UnicornFail\Emoji\EmojibaseInterface;
use UnicornFail\Emoji\EmojibaseShortcodeInterface;
use UnicornFail\Emoji\Exception\LocalePresetException;
use UnicornFail\Emoji\Parser;

class ConverterTest extends TestCase
{
    public const ENCODINGS = [
        'en' => [
            'raw'       => '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D',
            'html'      => '&#x1F64D;&#x1F3FF;&#x200D;&#x2642;&#xFE0F; is leaving on a &#x2708;️. Going to ' .
                           '&#x1F1E6;&#x1F1FA;. Might see some &#x1F998;! &#x2764; Remember to &#x1F4F1; &#x1F600;',
            'shortcode' => ':man-frowning-tone5: is leaving on a :airplane:️. Going to :flag-au:. ' .
                           'Might see some :kangaroo:! :red-heart: Remember to :mobile-phone: :grinning-face:',
            'unicode'   => '🙍🏿‍♂️ is leaving on a ✈️️. Going to 🇦🇺. Might see some 🦘! ❤️ Remember to 📱 😀',
        ],
    ];

    /** @var Converter */
    protected $converter;

    /**
     * @return mixed[]
     */
    public function providerEncodings(): array
    {
        $data                  = [];
        $data['T_EMOTICON']    = [Parser::T_EMOTICON, self::ENCODINGS['en']['raw']];
        $data['T_HTML_ENTITY'] = [Parser::T_HTML_ENTITY, self::ENCODINGS['en']['html']];
        $data['T_SHORTCODE']   = [Parser::T_SHORTCODE, self::ENCODINGS['en']['shortcode']];
        $data['T_UNICODE']     = [Parser::T_UNICODE, self::ENCODINGS['en']['unicode']];

        return $data;
    }

    /**
     * @return mixed[]
     */
    public function providerLocalPresets(): array
    {
        $data = [];
        foreach (EmojibaseInterface::SUPPORTED_LOCALES as $locale) {
            foreach (EmojibaseShortcodeInterface::PRESETS as $preset) {
                $label = \sprintf('%s:%s', $locale, $preset);
                if (\file_exists(\sprintf('%s/%s/%s.gz', Dataset::DIRECTORY, $locale, $preset))) {
                    $data[$label] = [$locale, $preset];
                } else {
                    $data[$label] = [
                        $locale,
                        $preset,
                        LocalePresetException::class,
                    ];
                }
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
        $data['AUTO']      = [['presentation' => EmojibaseInterface::AUTO], ':smiling-face:', '☺︎'];
        $data['EMOJI']     = [['presentation' => EmojibaseInterface::EMOJI], ':smiling-face:', '☺️'];
        $data['TEXT']      = [['presentation' => EmojibaseInterface::TEXT], ':smiling-face:', '☺︎'];

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

    public function testConvert(): void
    {
        $converter = new Converter();
        $actual    = $converter->convert(self::ENCODINGS['en']['raw']);
        $this->assertEquals(self::ENCODINGS['en']['unicode'], $actual);
    }

    /**
     * @dataProvider providerEncodings
     */
    public function testConvertToEncoding(int $tokenType, string $expected): void
    {
        $converter = new Converter();
        $actual    = null;
        switch ($tokenType) {
            case Parser::T_EMOTICON:
                $actual = $converter->convertToEmoticon(self::ENCODINGS['en']['raw']);
                break;

            case Parser::T_HTML_ENTITY:
                $actual = $converter->convertToHtml(self::ENCODINGS['en']['raw']);
                break;

            case Parser::T_SHORTCODE:
                $actual = $converter->convertToShortcode(self::ENCODINGS['en']['raw']);
                break;

            case Parser::T_UNICODE:
                $actual = $converter->convertToUnicode(self::ENCODINGS['en']['raw']);
                break;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider providerLocalPresets
     */
    public function testCreate(string $locale, string $preset, ?string $exception = null): void
    {
        if ($exception) {
            $this->expectException($exception);
            $this->expectExceptionMessage(\sprintf(
                "Attempted to load the locale \"%s\" dataset. However, the following preset(s) were unable to be loaded:\n%s",
                $locale,
                \sprintf('%s: The following file does not exist or is not readable: %s', $preset, \sprintf(
                    '%s/%s/%s.gz',
                    Dataset::DIRECTORY,
                    $locale,
                    $preset
                ))
            ));
        }

        $configuration = [
            'locale' => $locale,
            'native' => false,
            'preset' => $preset,
        ];
        $converter     = new Converter($configuration);
        $this->assertTrue($converter instanceof Converter);

        // The variable must be manually emptied after each assertion in order to avoid memory leaks between tests.
        $converter = null;
    }

    public function testExcludedShortcodes(): void
    {
        $converter = Converter::create(
            [
                'excludeShortcodes' => ['mobile-phone'],
            ]
        );
        $this->assertEquals(':iphone:', $converter->convert('📱', Parser::T_SHORTCODE));
    }

    /**
     * @dataProvider providerPresentation
     *
     * @param mixed[] $configuration
     */
    public function testPresentation(array $configuration, string $raw, string $expected): void
    {
        $converter = Converter::create($configuration);
        $actual    = $converter->convertToUnicode($raw);
        $this->assertSame($expected, $actual);
    }

    public function testReadme(): void
    {
        $converter = new Converter();

        $unicode = $converter->convert('We <3 :unicorn: :D!');
        $this->assertSame('We ❤️ 🦄 😀!', $unicode);

        $html = $converter->convertToHtml('We <3 :unicorn: :D!');
        $this->assertSame('We &#x2764; &#x1F984; &#x1F600;!', $html);

        $shortcode = $converter->convertToShortcode('We <3 :unicorn: :D!');
        $this->assertSame('We :red-heart: :unicorn-face: :grinning-face:!', $shortcode);
    }

    /**
     * @dataProvider providerShortcodePresets
     */
    public function testShortcodeToUnicodePresets(string $preset, string $contents): void
    {
        $converter = Converter::create(['preset' => $preset]);
        $expected  = \file_get_contents(__DIR__ . '/../fixtures/unicode.md');
        $actual    = $converter->convert($contents, Parser::T_UNICODE);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider providerShortcodePresets
     */
    public function testUnicodeToShortcodePresets(string $preset, string $expected): void
    {
        $converter = Converter::create(['preset' => $preset]);
        $contents  = \file_get_contents(__DIR__ . '/../fixtures/unicode.md');
        $actual    = $converter->convert($contents, Parser::T_SHORTCODE);
        $this->assertEquals($expected, $actual);
    }
}