<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit\Dataset;

use PHPUnit\Framework\TestCase;
use UnicornFail\Emoji\Dataset\Dataset;
use UnicornFail\Emoji\Dataset\Emoji;

class DatasetTest extends TestCase
{
    public function testArrayAccess(): void
    {
        $emoji   = new Emoji(EmojiTest::GRINNING_FACE);
        $dataset = new Dataset($emoji);

        $this->assertSame(EmojiTest::GRINNING_FACE['hexcode'], \current(\array_keys($dataset->getArrayCopy())));
        $this->assertEquals($dataset[EmojiTest::GRINNING_FACE['hexcode']], $emoji);
    }

    public function testCreate(): void
    {
        $dataset = new Dataset();
        $this->assertSame(0, $dataset->count());

        $emoji   = new Emoji(EmojiTest::GRINNING_FACE);
        $dataset = new Dataset($emoji);
        $this->assertSame(1, $dataset->count());

        $dataset = new Dataset([$emoji, $emoji]);
        $this->assertSame(1, $dataset->count());

        $this->assertEquals('UnicornFail\Emoji\Dataset\Dataset', \get_class($dataset));
        $this->assertTrue(new Dataset($emoji) instanceof Dataset);
        $this->assertTrue(new Dataset($dataset) instanceof Dataset);

        $this->expectExceptionObject(
            new \RuntimeException(\sprintf('Passed array item must be an instance of %s.', Emoji::class))
        );
        $this->assertTrue(new Dataset(1) instanceof Dataset);
    }

    public function testFilter(): void
    {
        $grinningFace = new Emoji(EmojiTest::GRINNING_FACE);
        $wavingHand   = new Emoji(EmojiTest::WAVING_HAND);
        $dataset      = new Dataset([$grinningFace, $wavingHand]);
        $this->assertSame(7, $dataset->count());

        $emoticons = $dataset->filter(
            static function (Emoji $emoji) {
                return $emoji->emoticon !== null;
            }
        )->indexBy('emoticon');
        $this->assertSame(1, $emoticons->count());
    }
}
