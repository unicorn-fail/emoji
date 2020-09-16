<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPStan\Testing\TestCase;
use UnicornFail\Emoji\Dataset;
use UnicornFail\Emoji\Emoji;
use UnicornFail\Emoji\Exception\FileNotFoundException;
use UnicornFail\Emoji\Exception\MalformedArchiveException;
use UnicornFail\Emoji\Exception\UnarchiveException;

class DatasetTest extends TestCase
{
    /** @var string[] */
    protected $temporaryFiles = [];

    protected function createTemporaryFile(): string
    {
        $temporaryFile          = \tempnam(\sys_get_temp_dir(), 'emoji');
        $this->temporaryFiles[] = $temporaryFile;

        return $temporaryFile;
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $temporaryFile) {
            @\unlink($temporaryFile);
        }
    }

    public function testArchive(): void
    {
        $temp    = $this->createTemporaryFile();
        $emoji   = new Emoji(EmojiTest::GRINNING_FACE);
        $dataset = new Dataset($emoji);
        \file_put_contents($temp, $dataset->archive());
        $this->assertNotEmpty(\filesize($temp));

        $archived = Dataset::unarchive($temp);
        $this->assertTrue($archived instanceof Dataset);
        $this->assertEquals($dataset->getArrayCopy(), $archived->getArrayCopy());
    }

    public function testEmptyArchive(): void
    {
        $temp = $this->createTemporaryFile();
        $this->expectException(UnarchiveException::class);
        $this->expectExceptionMessage(\sprintf('Empty or corrupted archive: %s.', $temp));
        Dataset::unarchive($temp);
    }

    public function testMalformedArchive(): void
    {
        $temp = $this->createTemporaryFile();
        \file_put_contents($temp, \gzencode(\file_get_contents(__FILE__), 9));
        $this->expectException(MalformedArchiveException::class);
        $this->expectExceptionMessage(\sprintf('Malformed archive %s. Perhaps it is corrupted or was archived using an older API. Try recreating the archive.', $temp));
        Dataset::unarchive($temp);
    }

    public function testMalformedArchive2(): void
    {
        $temp = $this->createTemporaryFile();
        \file_put_contents($temp, \gzencode(\serialize(['foo', 'bar', 'baz']), 9));
        $this->expectException(MalformedArchiveException::class);
        $this->expectExceptionMessage(\sprintf('Malformed archive %s. Perhaps it is corrupted or was archived using an older API. Try recreating the archive.', $temp));
        Dataset::unarchive($temp);
    }

    public function testMissingArchive(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('The following file does not exist or is not readable: foo-bar');
        Dataset::unarchive('foo-bar');
    }

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

        $this->assertTrue($dataset instanceof Dataset);
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
