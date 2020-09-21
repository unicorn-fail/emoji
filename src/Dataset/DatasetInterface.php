<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Dataset;

use UnicornFail\Emoji\Parser\Parser;

/**
 * @method Emoji[] getArrayCopy()
 * @method Emoji offsetGet()
 */
interface DatasetInterface extends \SeekableIterator, \ArrayAccess, \Serializable, \Countable
{
    public const DIRECTORY = __DIR__ . '/../../datasets';

    public static function unarchive(string $filename): Dataset;

    /**
     * @param string[] $indices
     *
     * @return false|string
     */
    public function archive(array $indices = Parser::INDICES);

    /**
     * @param callable(Emoji):bool $callback
     */
    public function filter(callable $callback): Dataset;

    public function indexBy(string $index = 'hexcode'): Dataset;
}
