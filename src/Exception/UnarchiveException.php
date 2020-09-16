<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Exception;

use Throwable;

class UnarchiveException extends \RuntimeException implements EmojiException
{
    public function __construct(string $filename, ?string $message = null, ?Throwable $previous = null)
    {
        if (! isset($message)) {
            $message = \sprintf('Empty or corrupted archive: %s.', $filename);
        }

        parent::__construct($message, 0, $previous);
    }
}
