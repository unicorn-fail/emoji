<?php

declare(strict_types=1);

namespace League\Emoji\Tests\Unit\Dataset;

use League\Configuration\Configuration;
use PHPUnit\Framework\TestCase;
use League\Emoji\Dataset\Dataset;
use League\Emoji\Dataset\Emoji;
use League\Emoji\Dataset\RuntimeDataset;
use League\Emoji\Emojibase\EmojibaseShortcodeInterface;
use League\Emoji\Environment\Environment;
use League\Emoji\Exception\FileNotFoundException;
use League\Emoji\Exception\MalformedArchiveException;
use League\Emoji\Exception\UnarchiveException;

class RuntimeDatasetTest extends TestCase
{
    /** @var string[] */
    protected $temporaryFiles = [];

    protected function createTemporaryFile(): string
    {
        $temporaryDir  = \sys_get_temp_dir();
        $temporaryFile = \tempnam($temporaryDir, 'emoji');

        if ($temporaryFile === false) {
            $temporaryFile = \sprintf('/%s/emoji-%s', $temporaryDir, \md5($this->getName()));
            \touch($temporaryFile);
        }

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
        \file_put_contents($temp, RuntimeDataset::archive($dataset));
        $this->assertNotEmpty(\filesize($temp));

        $archived = RuntimeDataset::unarchive($temp);
        $this->assertEquals('League\Emoji\Dataset\Dataset', \get_class($archived));
        $this->assertEquals($dataset->getArrayCopy(), $archived->getArrayCopy());
    }

    public function testEmptyArchive(): void
    {
        $temp = $this->createTemporaryFile();
        $this->expectException(UnarchiveException::class);
        $this->expectExceptionMessage(\sprintf('Empty or corrupted archive: %s.', $temp));
        RuntimeDataset::unarchive($temp);
    }

    public function testMalformedArchive(): void
    {
        $temp = $this->createTemporaryFile();

        \file_put_contents($temp, (string) \gzencode((string) \file_get_contents(__FILE__), 9));

        $this->expectException(MalformedArchiveException::class);
        $this->expectExceptionMessage(\sprintf('Malformed archive %s. Perhaps it is corrupted or was archived using an older API. Try recreating the archive.', $temp));
        RuntimeDataset::unarchive($temp);
    }

    public function testMalformedArchive2(): void
    {
        $temp = $this->createTemporaryFile();
        \file_put_contents($temp, \gzencode(\serialize(['foo', 'bar', 'baz']), 9));
        $this->expectException(MalformedArchiveException::class);
        $this->expectExceptionMessage(\sprintf('Malformed archive %s. Perhaps it is corrupted or was archived using an older API. Try recreating the archive.', $temp));
        RuntimeDataset::unarchive($temp);
    }

    public function testMissingArchive(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('The following file does not exist or is not readable: foo-bar');
        RuntimeDataset::unarchive('foo-bar');
    }

    public function testArrayObject(): void
    {
        $grinningFace = new Emoji(EmojiTest::GRINNING_FACE);
        $wavingHand   = new Emoji(EmojiTest::WAVING_HAND);
        $dataset      = new Dataset([$grinningFace, $wavingHand]);
        $runtime      = new RuntimeDataset(new Configuration(), $dataset);

        $this->assertTrue($runtime->offsetExists(EmojiTest::GRINNING_FACE['hexcode']));

        $this->assertSame(7, $runtime->count());
        $this->assertSame($grinningFace, $runtime->current());
        $this->assertSame(EmojiTest::GRINNING_FACE['hexcode'], $runtime->key());

        $runtime->seek(1);

        $this->assertSame($wavingHand, $runtime->current());
        $this->assertSame(EmojiTest::WAVING_HAND['hexcode'], $runtime->key());

        $runtime->rewind();

        $this->assertSame($grinningFace, $runtime->current());

        $runtime->next();
        $this->assertSame($wavingHand, $runtime->current());

        $this->assertTrue($runtime->valid());
    }

    public function testFilter(): void
    {
        $grinningFace = new Emoji(EmojiTest::GRINNING_FACE);
        $wavingHand   = new Emoji(EmojiTest::WAVING_HAND);
        $dataset      = new Dataset([$grinningFace, $wavingHand]);
        $runtime      = new RuntimeDataset(new Configuration(), $dataset);

        $this->assertSame(7, $runtime->count());

        $filtered = $runtime->filter(
            static function (Emoji $emoji) {
                return $emoji->emoticon !== null;
            }
        );

        $this->assertSame(RuntimeDataset::class, \get_class($filtered));

        $this->assertSame(1, $filtered->indexBy('emoticon')->count());
    }

    public function testGetPresets(): void
    {
        $environment = new Environment(['locale' => 'en', 'preset' => 'github']);
        $runtime     = $environment->getRuntimeDataset();

        $this->assertEquals([
            EmojibaseShortcodeInterface::PRESET_GITHUB,
        ], $runtime->getPresets());

        $environment = new Environment(['locale' => 'ja']);
        $runtime     = $environment->getRuntimeDataset();

        $this->assertEquals([
            EmojibaseShortcodeInterface::PRESET_CLDR_NATIVE,
            EmojibaseShortcodeInterface::PRESET_EMOJIBASE,
            EmojibaseShortcodeInterface::PRESET_CLDR,
        ], $runtime->getPresets());

        $environment = new Environment(['locale' => 'ja', 'native' => false]);
        $runtime     = $environment->getRuntimeDataset();

        $this->assertEquals([
            EmojibaseShortcodeInterface::PRESET_EMOJIBASE,
            EmojibaseShortcodeInterface::PRESET_CLDR,
        ], $runtime->getPresets());
    }

    /**
     * @return mixed[]
     */
    public function providerImmutabilityMethods(): array
    {
        $methods = [['offsetSet'], ['offsetUnset']];

        return (array) \array_combine(\array_map('current', $methods), $methods);
    }

    /**
     * @dataProvider providerImmutabilityMethods
     */
    public function testImmutability(string $method): void
    {
        $grinningFace = new Emoji(EmojiTest::GRINNING_FACE);
        $wavingHand   = new Emoji(EmojiTest::WAVING_HAND);
        $dataset      = new Dataset([$grinningFace, $wavingHand]);
        $runtime      = new RuntimeDataset(new Configuration(), $dataset);

        $this->expectExceptionObject(new \BadMethodCallException('Unable to modify immutable object.'));
        $runtime->$method('foo', 'bar');
    }
}
