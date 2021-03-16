<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Node;

use PHPUnit\Framework\TestCase;
use League\Emoji\Dataset\Emoji as DatasetEmoji;
use League\Emoji\Lexer\EmojiLexer;
use League\Emoji\Node\Emoji;

class EmojiTest extends TestCase
{
    /**
     * @dataProvider \League\Emoji\Tests\Unit\Dataset\EmojiTest::providerEmojis
     *
     * @param mixed[] $data
     * @param mixed[] $expectedData
     */
    public function testGet(array $data, array $expectedData): void
    {
        $this->assertEmojiData($data, $expectedData);
    }

    /**
     * @param mixed[] $data
     * @param mixed[] $expectedData
     */
    public function assertEmojiData(array $data, array $expectedData): void
    {
        $datasetEmoji = new DatasetEmoji($data);

        $parsedValue = (string) $data['emoji'];
        $emoji       = new Emoji(EmojiLexer::T_UNICODE, $parsedValue, $datasetEmoji);

        $this->assertSame(EmojiLexer::T_UNICODE, $emoji->getParsedType());
        $this->assertSame($parsedValue, $emoji->getParsedValue());

        // Test invalid skin tone.
        $this->assertNull($emoji->getSkin(-1));

        $this->assertSame(\get_class($datasetEmoji), DatasetEmoji::class);
        $this->assertSame(\get_class($emoji), Emoji::class);

        foreach ($expectedData as $property => $expected) {

            /** @var array<int, array<string, mixed>> $skinData */
            $skinData = isset($data['skins']) ? (array) $data['skins'] : [];
            switch ($property) {
                case 'skin':
                    if (isset($skinData) && \count($skinData) > 0) {
                        $expectedTone = (int) $expectedData;

                        /** @var ?Emoji $expectedToneEmoji */
                        $expectedToneEmoji = null;
                        if (isset($skinData[$expectedTone - 1])) {
                            /** @var mixed[] $skin */
                            $skin              = $skinData[$expectedTone - 1];
                            $expectedToneEmoji = new Emoji(EmojiLexer::T_UNICODE, (string) $skin['emoji'], new DatasetEmoji($skin));
                        }

                        $actualToneEmoji = $emoji->getSkin($expectedTone);
                        if ($expectedToneEmoji === null) {
                            $this->assertNull($actualToneEmoji);
                        } else {
                            $this->assertSame(\get_class($actualToneEmoji), Emoji::class, $property);
                            $this->assertEquals($expectedToneEmoji->getArrayCopy(), $actualToneEmoji->getArrayCopy(), $property);
                        }
                    } else {
                        $this->assertSame($expected, $emoji->$property, $property);
                    }

                    break;

                case 'skins':
                    $actualSkins = \array_map('iterator_to_array', $emoji->skins->getArrayCopy());

                    if (isset($expectedData['skins']) && \count($expectedData['skins']) > 0) {
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
}
