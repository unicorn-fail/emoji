<?php

declare(strict_types=1);

namespace League\Emoji\Exception;

use Throwable;

class FileNotFoundException extends \RuntimeException implements EmojiException
{
    public function __construct(string $filename, ?Throwable $previous = null)
    {
        parent::__construct(\sprintf('The following file does not exist or is not readable: %s', $filename), 0, $previous);
    }
}
