<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Extension;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Tests\Unit\EmojiConverterTest;
use UnicornFail\Emoji\TwemojiConverter;

class TwemojiExtensionTest extends TestCase
{
    /** @param array<string, mixed> $config */
    protected function convert(string $input, array $config = [], bool $setAsDefaultConversionType = true): string
    {
        $converter = TwemojiConverter::create($config, $setAsDefaultConversionType);

        return (string) $converter->convert($input);
    }

    /**
     * @param string|string[]      $conversionTypes
     * @param array<string, mixed> $config
     */
    protected function convertTo($conversionTypes, string $input, array $config = [], bool $setAsDefaultConversionType = true): string
    {
        $config['convert'] = $conversionTypes;

        return $this->convert($input, $config, $setAsDefaultConversionType);
    }

    public function testConvert(): void
    {
        $raw      = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';
        $expected = '<img src="https://twemoji.maxcdn.com/v/latest/svg/1f64d-1f3ff-200d-2642-fe0f.svg" alt="man frowning: dark skin tone" title="man frowning: dark skin tone" class="twemoji twemoji-man-frowning-dark-skin-tone" style="width: 1em; height: 1em; vertical-align: middle;" /> is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/svg/2708.svg" alt="airplane" title="airplane" class="twemoji twemoji-airplane" style="width: 1em; height: 1em; vertical-align: middle;" />️. Going to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f1e6-1f1fa.svg" alt="flag: Australia" title="flag: Australia" class="twemoji twemoji-flag-Australia" style="width: 1em; height: 1em; vertical-align: middle;" />. Might see some <img src="https://twemoji.maxcdn.com/v/latest/svg/1f998.svg" alt="kangaroo" title="kangaroo" class="twemoji twemoji-kangaroo" style="width: 1em; height: 1em; vertical-align: middle;" />! <img src="https://twemoji.maxcdn.com/v/latest/svg/2764.svg" alt="red heart" title="red heart" class="twemoji twemoji-red-heart" style="width: 1em; height: 1em; vertical-align: middle;" /> Remember to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f4f1.svg" alt="mobile phone" title="mobile phone" class="twemoji twemoji-mobile-phone" style="width: 1em; height: 1em; vertical-align: middle;" /> <img src="https://twemoji.maxcdn.com/v/latest/svg/1f600.svg" alt="grinning face" title="grinning face" class="twemoji twemoji-grinning-face" style="width: 1em; height: 1em; vertical-align: middle;" />';

        $actual = $this->convert($raw);
        $this->assertEquals($expected, $actual);
    }

    public function testPartialConvert(): void
    {
        $raw      = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';
        $expected = '<img src="https://twemoji.maxcdn.com/v/latest/svg/1f64d-1f3ff-200d-2642-fe0f.svg" alt="man frowning: dark skin tone" title="man frowning: dark skin tone" class="twemoji twemoji-man-frowning-dark-skin-tone" style="width: 1em; height: 1em; vertical-align: middle;" /> is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/svg/2708.svg" alt="airplane" title="airplane" class="twemoji twemoji-airplane" style="width: 1em; height: 1em; vertical-align: middle;" />️. Going to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f1e6-1f1fa.svg" alt="flag: Australia" title="flag: Australia" class="twemoji twemoji-flag-Australia" style="width: 1em; height: 1em; vertical-align: middle;" />. Might see some :kangaroo:! <img src="https://twemoji.maxcdn.com/v/latest/svg/2764.svg" alt="red heart" title="red heart" class="twemoji twemoji-red-heart" style="width: 1em; height: 1em; vertical-align: middle;" /> Remember to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f4f1.svg" alt="mobile phone" title="mobile phone" class="twemoji twemoji-mobile-phone" style="width: 1em; height: 1em; vertical-align: middle;" /> <img src="https://twemoji.maxcdn.com/v/latest/svg/1f600.svg" alt="grinning face" title="grinning face" class="twemoji twemoji-grinning-face" style="width: 1em; height: 1em; vertical-align: middle;" />';

        // Keep shortcodes (perhaps parsed using something else).
        $conversionTypes = [
            TwemojiConverter::SHORTCODE => TwemojiConverter::SHORTCODE,
        ];

        $actual = $this->convertTo($conversionTypes, $raw);
        $this->assertEquals($expected, $actual);
    }

    public function testSetAsDefaultConversionType(): void
    {
        $raw      = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';
        $expected = EmojiConverterTest::ENCODINGS['en']['unicode'];

        // Ensure twemoji isn't set as the default conversion type.
        $actual = $this->convert($raw, [], false);
        $this->assertEquals($expected, $actual);

        $raw      = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';
        $expected = '🙍🏿‍♂️ is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/svg/2708.svg" alt="airplane" title="airplane" class="twemoji twemoji-airplane" style="width: 1em; height: 1em; vertical-align: middle;" />️. Going to 🇦🇺. Might see some 🦘! ❤️ Remember to 📱 😀';

        // Ensure twemoji isn't set as the default conversion type.
        $actual = $this->convert($raw, [
            'convert' => [
                TwemojiConverter::HTML_ENTITY => TwemojiConverter::CONVERSION_TYPE,
            ],
        ], false);
        $this->assertEquals($expected, $actual);
    }

    public function testPngType(): void
    {
        $raw      = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';
        $expected = '<img src="https://twemoji.maxcdn.com/v/latest/72x72/1f64d-1f3ff-200d-2642-fe0f.png" alt="man frowning: dark skin tone" title="man frowning: dark skin tone" class="twemoji twemoji-man-frowning-dark-skin-tone" style="width: 1em; height: 1em; vertical-align: middle;" /> is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/72x72/2708.png" alt="airplane" title="airplane" class="twemoji twemoji-airplane" style="width: 1em; height: 1em; vertical-align: middle;" />️. Going to <img src="https://twemoji.maxcdn.com/v/latest/72x72/1f1e6-1f1fa.png" alt="flag: Australia" title="flag: Australia" class="twemoji twemoji-flag-Australia" style="width: 1em; height: 1em; vertical-align: middle;" />. Might see some <img src="https://twemoji.maxcdn.com/v/latest/72x72/1f998.png" alt="kangaroo" title="kangaroo" class="twemoji twemoji-kangaroo" style="width: 1em; height: 1em; vertical-align: middle;" />! <img src="https://twemoji.maxcdn.com/v/latest/72x72/2764.png" alt="red heart" title="red heart" class="twemoji twemoji-red-heart" style="width: 1em; height: 1em; vertical-align: middle;" /> Remember to <img src="https://twemoji.maxcdn.com/v/latest/72x72/1f4f1.png" alt="mobile phone" title="mobile phone" class="twemoji twemoji-mobile-phone" style="width: 1em; height: 1em; vertical-align: middle;" /> <img src="https://twemoji.maxcdn.com/v/latest/72x72/1f600.png" alt="grinning face" title="grinning face" class="twemoji twemoji-grinning-face" style="width: 1em; height: 1em; vertical-align: middle;" />';

        $actual = $this->convert($raw, [
            'twemoji' => [
                'type' => 'png',
            ],
        ]);
        $this->assertEquals($expected, $actual);
    }

    public function testInlineSize(): void
    {
        $raw = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';

        $expected = '<img src="https://twemoji.maxcdn.com/v/latest/svg/1f64d-1f3ff-200d-2642-fe0f.svg" alt="man frowning: dark skin tone" title="man frowning: dark skin tone" class="twemoji twemoji-man-frowning-dark-skin-tone" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" /> is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/svg/2708.svg" alt="airplane" title="airplane" class="twemoji twemoji-airplane" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" />️. Going to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f1e6-1f1fa.svg" alt="flag: Australia" title="flag: Australia" class="twemoji twemoji-flag-Australia" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" />. Might see some <img src="https://twemoji.maxcdn.com/v/latest/svg/1f998.svg" alt="kangaroo" title="kangaroo" class="twemoji twemoji-kangaroo" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" />! <img src="https://twemoji.maxcdn.com/v/latest/svg/2764.svg" alt="red heart" title="red heart" class="twemoji twemoji-red-heart" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" /> Remember to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f4f1.svg" alt="mobile phone" title="mobile phone" class="twemoji twemoji-mobile-phone" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" /> <img src="https://twemoji.maxcdn.com/v/latest/svg/1f600.svg" alt="grinning face" title="grinning face" class="twemoji twemoji-grinning-face" style="width: 2.5rem; height: 2.5rem; vertical-align: middle;" />';
        $actual   = $this->convert($raw, [
            'twemoji' => [
                'size' => '2.5rem',
            ],
        ]);
        $this->assertEquals($expected, $actual);

        $expected = '<img src="https://twemoji.maxcdn.com/v/latest/svg/1f64d-1f3ff-200d-2642-fe0f.svg" alt="man frowning: dark skin tone" title="man frowning: dark skin tone" class="twemoji twemoji-man-frowning-dark-skin-tone" style="width: 2.5em; height: 2.5em; vertical-align: middle;" /> is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/svg/2708.svg" alt="airplane" title="airplane" class="twemoji twemoji-airplane" style="width: 2.5em; height: 2.5em; vertical-align: middle;" />️. Going to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f1e6-1f1fa.svg" alt="flag: Australia" title="flag: Australia" class="twemoji twemoji-flag-Australia" style="width: 2.5em; height: 2.5em; vertical-align: middle;" />. Might see some <img src="https://twemoji.maxcdn.com/v/latest/svg/1f998.svg" alt="kangaroo" title="kangaroo" class="twemoji twemoji-kangaroo" style="width: 2.5em; height: 2.5em; vertical-align: middle;" />! <img src="https://twemoji.maxcdn.com/v/latest/svg/2764.svg" alt="red heart" title="red heart" class="twemoji twemoji-red-heart" style="width: 2.5em; height: 2.5em; vertical-align: middle;" /> Remember to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f4f1.svg" alt="mobile phone" title="mobile phone" class="twemoji twemoji-mobile-phone" style="width: 2.5em; height: 2.5em; vertical-align: middle;" /> <img src="https://twemoji.maxcdn.com/v/latest/svg/1f600.svg" alt="grinning face" title="grinning face" class="twemoji twemoji-grinning-face" style="width: 2.5em; height: 2.5em; vertical-align: middle;" />';
        $actual   = $this->convert($raw, [
            'twemoji' => [
                'size' => 2.5,
            ],
        ]);
        $this->assertEquals($expected, $actual);
    }

    public function testNonInlineSize(): void
    {
        $raw      = '🙍🏿‍♂️ is leaving on a &#x2708;️. Going to 🇦🇺. Might see some :kangaroo:! <3 Remember to 📱 :D';
        $expected = '<img src="https://twemoji.maxcdn.com/v/latest/svg/1f64d-1f3ff-200d-2642-fe0f.svg" alt="man frowning: dark skin tone" title="man frowning: dark skin tone" class="twemoji twemoji-man-frowning-dark-skin-tone" height="72" width="72" /> is leaving on a <img src="https://twemoji.maxcdn.com/v/latest/svg/2708.svg" alt="airplane" title="airplane" class="twemoji twemoji-airplane" height="72" width="72" />️. Going to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f1e6-1f1fa.svg" alt="flag: Australia" title="flag: Australia" class="twemoji twemoji-flag-Australia" height="72" width="72" />. Might see some <img src="https://twemoji.maxcdn.com/v/latest/svg/1f998.svg" alt="kangaroo" title="kangaroo" class="twemoji twemoji-kangaroo" height="72" width="72" />! <img src="https://twemoji.maxcdn.com/v/latest/svg/2764.svg" alt="red heart" title="red heart" class="twemoji twemoji-red-heart" height="72" width="72" /> Remember to <img src="https://twemoji.maxcdn.com/v/latest/svg/1f4f1.svg" alt="mobile phone" title="mobile phone" class="twemoji twemoji-mobile-phone" height="72" width="72" /> <img src="https://twemoji.maxcdn.com/v/latest/svg/1f600.svg" alt="grinning face" title="grinning face" class="twemoji twemoji-grinning-face" height="72" width="72" />';

        $actual = $this->convert($raw, [
            'twemoji' => [
                'inline' => false,
                'size'   => 72,
            ],
        ]);
        $this->assertEquals($expected, $actual);
    }
}
