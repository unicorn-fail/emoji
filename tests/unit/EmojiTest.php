<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPUnit\Framework\TestCase;
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
        foreach ($expectedData as $property => $expected) {
            switch ($property) {
                case 'skin':
                    if (isset($data['skins']) && \count($data['skins']) > 0) {
                        $expectedTone      = (int) $expectedData;
                        $expectedToneEmoji = null;
                        if (isset($data['skins'][$expectedTone - 1])) {
                            $expectedToneEmoji = new Emoji($data['skins'][$expectedTone - 1]);
                        }

                        $actualToneEmoji = $emoji->getSkin($expectedTone);
                        $this->assertTrue($actualToneEmoji instanceof Emoji, $property);
                        $this->assertEquals($expectedToneEmoji->getArrayCopy(), $actualToneEmoji->getArrayCopy(), $property);
                    } else {
                        $this->assertSame($expected, $emoji->$property, $property);
                    }

                    break;

                case 'skins':
                    $actualSkins = \array_map('iterator_to_array', $emoji->skins->getArrayCopy());

                    if (isset($expectedData['skins']) && \count($expectedData['skins']) > 0) {
                        $skinData      = isset($data['skins']) ? (array) $data['skins'] : [];
                        $expectedSkins = isset($expectedData['skins']) ? (array) $expectedData['skins'] : [];
                        foreach ($expectedSkins as $expectedSkinData) {
                            $this->assertEmojiData(\array_shift($skinData), $expectedSkinData);
                        }
                    } else {
                        $this->assertSame($expected, $actualSkins, $property);
                    }

                    break;

                default:
                    $this->assertSame($expected, $emoji->$property, $property);
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
                'annotation' => 'grinning face',
                'emoji'      => '😀',
                'emoticon'   => ':D',
                'gender'     => null,
                'group'      => 0,
                'hexcode'    => '1F600',
                'htmlEntity' => '&#x1F600;',
                'order'      => 1,
                'shortcode'  => 'grinning-face',
                'shortcodes' => ['grinning-face'],
                'skin'       => null,
                'skins'      => [],
                'subgroup'   => 0,
                'tags'       => [
                    'face',
                    'grin',
                ],
                'text'       => null,
                'tone'       => [],
                'type'       => 1,
                'unicode'    => '😀',
                'version'    => 1.0,
            ],
        ];

        $data['waving hand'] = [
            self::WAVING_HAND,
            [
                'annotation' => 'waving hand',
                'emoji'      => '👋',
                'emoticon'   => null,
                'gender'     => null,
                'group'      => 1,
                'hexcode'    => '1F44B',
                'htmlEntity' => '&#x1F44B;',
                'order'      => 163,
                'shortcode'  => 'waving-hand',
                'shortcodes' => ['waving-hand'],
                'skin'       => 1,
                'skins'      => [
                    [
                        'annotation' => 'waving hand: light skin tone',
                        'emoji'      => '👋🏻',
                        'emoticon'   => null,
                        'gender'     => null,
                        'group'      => 1,
                        'hexcode'    => '1F44B-1F3FB',
                        'htmlEntity' => '&#x1F44B;&#x1F3FB;',
                        'order'      => 164,
                        'shortcode'  => 'waving-hand-tone1',
                        'shortcodes' => ['waving-hand-tone1'],
                        'skin'       => null,
                        'skins'      => [],
                        'subgroup'   => 15,
                        'tags'       => [],
                        'text'       => null,
                        'tone'       => [1],
                        'type'       => 1,
                        'unicode'    => '👋🏻',
                        'version'    => 1.0,
                    ],
                    [
                        'annotation' => 'waving hand: medium-light skin tone',
                        'emoji'      => '👋🏼',
                        'emoticon'   => null,
                        'gender'     => null,
                        'group'      => 1,
                        'hexcode'    => '1F44B-1F3FC',
                        'htmlEntity' => '&#x1F44B;&#x1F3FC;',
                        'order'      => 165,
                        'shortcode'  => 'waving-hand-tone2',
                        'shortcodes' => ['waving-hand-tone2'],
                        'skin'       => null,
                        'skins'      => [],
                        'subgroup'   => 15,
                        'tags'       => [],
                        'text'       => null,
                        'tone'       => [2],
                        'type'       => 1,
                        'unicode'    => '👋🏼',
                        'version'    => 1.0,
                    ],
                    [
                        'annotation' => 'waving hand: medium skin tone',
                        'emoji'      => '👋🏽',
                        'emoticon'   => null,
                        'gender'     => null,
                        'group'      => 1,
                        'hexcode'    => '1F44B-1F3FD',
                        'htmlEntity' => '&#x1F44B;&#x1F3FD;',
                        'order'      => 166,
                        'shortcode'  => 'waving-hand-tone3',
                        'shortcodes' => ['waving-hand-tone3'],
                        'skin'       => null,
                        'skins'      => [],
                        'subgroup'   => 15,
                        'tags'       => [],
                        'text'       => null,
                        'tone'       => [3],
                        'type'       => 1,
                        'unicode'    => '👋🏽',
                        'version'    => 1.0,
                    ],
                    [
                        'annotation' => 'waving hand: medium-dark skin tone',
                        'emoji'      => '👋🏾',
                        'emoticon'   => null,
                        'gender'     => null,
                        'group'      => 1,
                        'hexcode'    => '1F44B-1F3FE',
                        'htmlEntity' => '&#x1F44B;&#x1F3FE;',
                        'order'      => 167,
                        'shortcode'  => 'waving-hand-tone4',
                        'shortcodes' => ['waving-hand-tone4'],
                        'skin'       => null,
                        'skins'      => [],
                        'subgroup'   => 15,
                        'tags'       => [],
                        'text'       => null,
                        'tone'       => [4],
                        'type'       => 1,
                        'unicode'    => '👋🏾',
                        'version'    => 1.0,
                    ],
                    [
                        'annotation' => 'waving hand: dark skin tone',
                        'emoji'      => '👋🏿',
                        'emoticon'   => null,
                        'gender'     => null,
                        'group'      => 1,
                        'hexcode'    => '1F44B-1F3FF',
                        'htmlEntity' => '&#x1F44B;&#x1F3FF;',
                        'order'      => 168,
                        'shortcode'  => 'waving-hand-tone5',
                        'shortcodes' => ['waving-hand-tone5'],
                        'skin'       => null,
                        'skins'      => [],
                        'subgroup'   => 15,
                        'tags'       => [],
                        'text'       => null,
                        'tone'       => [5],
                        'type'       => 1,
                        'unicode'    => '👋🏿',
                        'version'    => 1.0,
                    ],
                ],
                'subgroup'   => 15,
                'tags'       => [
                    'hand',
                    'wave',
                    'waving',
                ],
                'text'       => null,
                'tone'       => [],
                'type'       => 1,
                'unicode'    => '👋',
                'version'    => 0.6,
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
        foreach ($emoji as $key => $actual) {
            if (! isset(self::GRINNING_FACE[$key]) || \in_array($key, $ignoreKeys, true)) {
                continue;
            }

            $expected = self::GRINNING_FACE[$key];
            if ($expected === '') {
                $expected = null;
            }

            $this->assertSame($expected, $actual, $key);
        }

        $this->assertSame(self::GRINNING_FACE['annotation'], $emoji['annotation']);

        // ::offsetExists
        $this->assertTrue(isset($emoji['annotation']));

        // ::offsetGet
        $this->assertSame([], $emoji['tone']);

        // Ensure getMethods are accessible.
        $emoji = new Emoji(self::GRINNING_FACE);
        $this->assertSame('&#x' . self::GRINNING_FACE['hexcode'] . ';', $emoji->getHtmlEntity);
        $this->assertSame('&#x' . self::GRINNING_FACE['hexcode'] . ';', $emoji['htmlEntity']);

        $this->expectExceptionObject(new \OutOfRangeException('Unknown property: foo'));
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
        $expected = '6390f5490086baed7ac4ffb76cf4f06647da58ad7dce460a1fb8c672a6af8a91';

        // Apparently serialization spits out something a bit different in PHP 7.4+.
        if (\version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $expected = '56ba0c30f33f29f6c39197ef09fe531f9127efb795de3473e9b1fb840c1f3a1d';
        }

        $this->assertSame($expected, $actual);
    }

    public function testToString(): void
    {
        $emoji = new Emoji(self::GRINNING_FACE);
        $this->assertSame(self::GRINNING_FACE['emoji'], (string) $emoji);
    }
}
