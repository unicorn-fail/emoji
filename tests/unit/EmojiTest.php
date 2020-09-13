<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPStan\Testing\TestCase;
use UnicornFail\Emoji\Emoji;

class EmojiTest extends TestCase
{
    public const GRINNING_FACE = [
        'annotation' => 'grinning face',
        'hexcode'    => '1F600',
        'tags'       => [
            'face',
            'grin',
        ],
        'emoji'      => '😀',
        'text'       => '',
        'type'       => 1,
        'order'      => 1,
        'group'      => 0,
        'shortcodes' => ['grinning_face'],
        'subgroup'   => 0,
        'version'    => 1,
        'emoticon'   => ':D',
    ];

    public const WAVING_HAND = [
        'annotation' => 'waving hand',
        'hexcode'    => '1F44B',
        'tags'       => [
            'hand',
            'wave',
            'waving',
        ],
        'emoji'      => '👋',
        'text'       => '',
        'type'       => 1,
        'order'      => 163,
        'group'      => 1,
        'shortcodes' => ['waving_hand'],
        'subgroup'   => 15,
        'version'    => 0.6,
        'skins'      => [
            [
                'annotation' => 'waving hand: light skin tone',
                'hexcode'    => '1F44B-1F3FB',
                'emoji'      => '👋🏻',
                'text'       => '',
                'type'       => 1,
                'order'      => 164,
                'group'      => 1,
                'shortcodes' => ['waving_hand_tone1'],
                'subgroup'   => 15,
                'version'    => 1,
                'tone'       => 1,
            ],
            [
                'annotation' => 'waving hand: medium-light skin tone',
                'hexcode'    => '1F44B-1F3FC',
                'emoji'      => '👋🏼',
                'text'       => '',
                'type'       => 1,
                'order'      => 165,
                'group'      => 1,
                'shortcodes' => ['waving_hand_tone2'],
                'subgroup'   => 15,
                'version'    => 1,
                'tone'       => 2,
            ],
            [
                'annotation' => 'waving hand: medium skin tone',
                'hexcode'    => '1F44B-1F3FD',
                'emoji'      => '👋🏽',
                'text'       => '',
                'type'       => 1,
                'order'      => 166,
                'group'      => 1,
                'shortcodes' => ['waving_hand_tone3'],
                'subgroup'   => 15,
                'version'    => 1,
                'tone'       => 3,
            ],
            [
                'annotation' => 'waving hand: medium-dark skin tone',
                'hexcode'    => '1F44B-1F3FE',
                'emoji'      => '👋🏾',
                'text'       => '',
                'type'       => 1,
                'order'      => 167,
                'group'      => 1,
                'shortcodes' => ['waving_hand_tone4'],
                'subgroup'   => 15,
                'version'    => 1,
                'tone'       => 4,
            ],
            [
                'annotation' => 'waving hand: dark skin tone',
                'hexcode'    => '1F44B-1F3FF',
                'emoji'      => '👋🏿',
                'text'       => '',
                'type'       => 1,
                'order'      => 168,
                'group'      => 1,
                'shortcodes' => ['waving_hand_tone5'],
                'subgroup'   => 15,
                'version'    => 1,
                'tone'       => 5,
            ],
        ],
    ];

    /**
     * @param mixed[] $data
     * @param mixed[] $expectedData
     */
    protected function assertEmojiData(array $data, array $expectedData): void
    {
        $emoji = new Emoji($data);
        $this->assertTrue($emoji instanceof Emoji);
        foreach ($expectedData as $method => $expected) {
            switch ($method) {
                case 'getSkin':
                    if (isset($data['skins']) && \count($data['skins']) > 0) {
                        $expectedTone      = (int) $expectedData;
                        $expectedToneEmoji = null;
                        if (isset($data['skins'][$expectedTone - 1])) {
                            $expectedToneEmoji = new Emoji($data['skins'][$expectedTone - 1]);
                        }

                        $actualToneEmoji = $emoji->getSkin($expectedTone);
                        $this->assertTrue($actualToneEmoji instanceof Emoji, $method);
                        $this->assertEquals($expectedToneEmoji->toArray(), $actualToneEmoji->toArray(), $method);
                    } else {
                        $this->assertSame($expected, $emoji->$method(), $method);
                    }

                    break;

                case 'getSkins':
                    $actualSkins = \array_map('iterator_to_array', $emoji->getSkins()->getArrayCopy());

                    if (isset($expectedData['getSkins']) && \count($expectedData['getSkins']) > 0) {
                        $skinData      = isset($data['skins']) ? (array) $data['skins'] : [];
                        $expectedSkins = isset($expectedData['getSkins']) ? (array) $expectedData['getSkins'] : [];
                        foreach ($expectedSkins as $expectedSkinData) {
                            $this->assertEmojiData(\array_shift($skinData), $expectedSkinData);
                        }
                    } else {
                        $this->assertSame($expected, $actualSkins, $method);
                    }

                    break;

                default:
                    $this->assertSame($expected, $emoji->$method(), $method);
            }
        }
    }

    /**
     * @return mixed[]
     */
    public static function providerEmojis(): array
    {
        $data['grinning face'] = [
            self::GRINNING_FACE,
            [
                'getAnnotation' => 'grinning face',
                'getEmoji'      => '😀',
                'getEmoticon'   => ':D',
                'getGender'     => null,
                'getGroup'      => 0,
                'getHexcode'    => '1F600',
                'getHtmlEntity' => '&#x1F600;',
                'getOrder'      => 1,
                'getShortcode'  => 'grinning-face',
                'getShortcodes' => ['grinning-face'],
                'getSkin'       => null,
                'getSkins'      => [],
                'getSubgroup'   => 0,
                'getTags'       => [
                    'face',
                    'grin',
                ],
                'getText'       => null,
                'getTone'       => [],
                'getType'       => 1,
                'getUnicode'    => '😀',
                'getVersion'    => 1.0,
            ],
        ];

        $data['waving hand'] = [
            self::WAVING_HAND,
            [
                'getAnnotation' => 'waving hand',
                'getEmoji'      => '👋',
                'getEmoticon'   => null,
                'getGender'     => null,
                'getGroup'      => 1,
                'getHexcode'    => '1F44B',
                'getHtmlEntity' => '&#x1F44B;',
                'getOrder'      => 163,
                'getShortcode'  => 'waving-hand',
                'getShortcodes' => ['waving-hand'],
                'getSkin'       => 1,
                'getSkins'      => [
                    [
                        'getAnnotation' => 'waving hand: light skin tone',
                        'getEmoji'      => '👋🏻',
                        'getEmoticon'   => null,
                        'getGender'     => null,
                        'getGroup'      => 1,
                        'getHexcode'    => '1F44B-1F3FB',
                        'getHtmlEntity' => '&#x1F44B;&#x1F3FB;',
                        'getOrder'      => 164,
                        'getShortcode'  => 'waving-hand-tone1',
                        'getShortcodes' => ['waving-hand-tone1'],
                        'getSkin'       => null,
                        'getSkins'      => [],
                        'getSubgroup'   => 15,
                        'getTags'       => [],
                        'getText'       => null,
                        'getTone'       => [1],
                        'getType'       => 1,
                        'getUnicode'    => '👋🏻',
                        'getVersion'    => 1.0,
                    ],
                    [
                        'getAnnotation' => 'waving hand: medium-light skin tone',
                        'getEmoji'      => '👋🏼',
                        'getEmoticon'   => null,
                        'getGender'     => null,
                        'getGroup'      => 1,
                        'getHexcode'    => '1F44B-1F3FC',
                        'getHtmlEntity' => '&#x1F44B;&#x1F3FC;',
                        'getOrder'      => 165,
                        'getShortcode'  => 'waving-hand-tone2',
                        'getShortcodes' => ['waving-hand-tone2'],
                        'getSkin'       => null,
                        'getSkins'      => [],
                        'getSubgroup'   => 15,
                        'getTags'       => [],
                        'getText'       => null,
                        'getTone'       => [2],
                        'getType'       => 1,
                        'getUnicode'    => '👋🏼',
                        'getVersion'    => 1.0,
                    ],
                    [
                        'getAnnotation' => 'waving hand: medium skin tone',
                        'getEmoji'      => '👋🏽',
                        'getEmoticon'   => null,
                        'getGender'     => null,
                        'getGroup'      => 1,
                        'getHexcode'    => '1F44B-1F3FD',
                        'getHtmlEntity' => '&#x1F44B;&#x1F3FD;',
                        'getOrder'      => 166,
                        'getShortcode'  => 'waving-hand-tone3',
                        'getShortcodes' => ['waving-hand-tone3'],
                        'getSkin'       => null,
                        'getSkins'      => [],
                        'getSubgroup'   => 15,
                        'getTags'       => [],
                        'getText'       => null,
                        'getTone'       => [3],
                        'getType'       => 1,
                        'getUnicode'    => '👋🏽',
                        'getVersion'    => 1.0,
                    ],
                    [
                        'getAnnotation' => 'waving hand: medium-dark skin tone',
                        'getEmoji'      => '👋🏾',
                        'getEmoticon'   => null,
                        'getGender'     => null,
                        'getGroup'      => 1,
                        'getHexcode'    => '1F44B-1F3FE',
                        'getHtmlEntity' => '&#x1F44B;&#x1F3FE;',
                        'getOrder'      => 167,
                        'getShortcode'  => 'waving-hand-tone4',
                        'getShortcodes' => ['waving-hand-tone4'],
                        'getSkin'       => null,
                        'getSkins'      => [],
                        'getSubgroup'   => 15,
                        'getTags'       => [],
                        'getText'       => null,
                        'getTone'       => [4],
                        'getType'       => 1,
                        'getUnicode'    => '👋🏾',
                        'getVersion'    => 1.0,
                    ],
                    [
                        'getAnnotation' => 'waving hand: dark skin tone',
                        'getEmoji'      => '👋🏿',
                        'getEmoticon'   => null,
                        'getGender'     => null,
                        'getGroup'      => 1,
                        'getHexcode'    => '1F44B-1F3FF',
                        'getHtmlEntity' => '&#x1F44B;&#x1F3FF;',
                        'getOrder'      => 168,
                        'getShortcode'  => 'waving-hand-tone5',
                        'getShortcodes' => ['waving-hand-tone5'],
                        'getSkin'       => null,
                        'getSkins'      => [],
                        'getSubgroup'   => 15,
                        'getTags'       => [],
                        'getText'       => null,
                        'getTone'       => [5],
                        'getType'       => 1,
                        'getUnicode'    => '👋🏿',
                        'getVersion'    => 1.0,
                    ],
                ],
                'getSubgroup'   => 15,
                'getTags'       => [
                    'hand',
                    'wave',
                    'waving',
                ],
                'getText'       => null,
                'getTone'       => [],
                'getType'       => 1,
                'getUnicode'    => '👋',
                'getVersion'    => 0.6,
            ],
        ];

        return $data;
    }

    public function testArrayAccess(): void
    {
        // The following properties will have been normalized/transformed when an Emoji is created.
        // Just ignore them as this test is mostly about ensuring array iteration and access works properly.
        $ignoreKeys = ['shortcodes', 'skins', 'tone', 'version'];

        $emoji = new Emoji(self::GRINNING_FACE);
        foreach ($emoji as $key => $value) {
            if (\in_array($key, $ignoreKeys, true)) {
                continue;
            }

            $this->assertSame(self::GRINNING_FACE[$key], $value, $key);
        }

        $this->assertSame(self::GRINNING_FACE['annotation'], $emoji['annotation']);

        // ::offsetExists
        $this->assertTrue(isset($emoji['annotation']));

        // ::offsetGet
        // ::offsetSet
        $emoji['tone'] = 1;
        $this->assertSame([1], $emoji['tone']);

        // ::offsetUnset
        unset($emoji['annotation']);
        $this->assertNull($emoji->getAnnotation());

        // ::offsetExists
        $this->assertFalse(isset($emoji['annotation']));

        // Skins ::offsetSet
        $emoji2         = new Emoji(self::GRINNING_FACE);
        $emoji['skins'] = [$emoji2];
        $this->assertSame($emoji2, \current($emoji->getSkins()->getArrayCopy()));

        // Ensure getMethods are accessible.
        $emoji = new Emoji(self::GRINNING_FACE);
        $this->assertSame('&#x' . self::GRINNING_FACE['hexcode'] . ';', $emoji['getHtmlEntity']);
        $this->assertSame('&#x' . self::GRINNING_FACE['hexcode'] . ';', $emoji['htmlEntity']);

        $emoji['foo'] = 'bar';

        $this->expectExceptionObject(new \RuntimeException('Unknown property: foo'));
        $this->assertNull($emoji['foo']);
    }

    /**
     * @dataProvider providerEmojis
     *
     * @param mixed[] $data
     * @param mixed[] $expectedData
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function testCreate(array $data, array $expectedData): void
    {
        $emoji = new Emoji($data);
        $this->assertTrue($emoji instanceof Emoji);
        $this->assertTrue(Emoji::create($emoji) instanceof Emoji);
    }

    /**
     * @dataProvider providerEmojis
     *
     * @param mixed[] $data
     * @param mixed[] $expectedData
     */
    public function testGet(array $data, array $expectedData): void
    {
        $this->assertEmojiData($data, $expectedData);
    }

    public function testSerialize(): void
    {
        $emoji    = new Emoji(self::GRINNING_FACE);
        $actual   = \hash('sha256', \serialize($emoji));
        $expected = 'df251ebc9b95afbba481069cea8b6d907fe00775ec3e7f6b9a23eeee4f39fdc5';

        // Apparently serialization spits out something a bit different in PHP 7.4+.
        if (\version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $expected = '9c08dc267e644302994e0871c79cbe7329854e88a92dfb664da7f088af80b98a';
        }

        $this->assertSame($expected, $actual);
    }

    public function testToString(): void
    {
        $emoji = new Emoji(self::GRINNING_FACE);
        $this->assertSame(self::GRINNING_FACE['emoji'], (string) $emoji);
    }
}
