<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Exception;

use Throwable;

class UnarchiveException extends \RuntimeException implements EmojiException
{
    public function __construct(string $filename, ?Throwable $previous = null)
    {
        parent::__construct(\sprintf('Unable to unarchive %s. Perhaps it is corrupted or was archived using an older API. Try recreating the archive', $filename), 0, $previous);
    }
}
