<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Exception;

use Throwable;

class MalformedArchiveException extends UnarchiveException
{
    public function __construct(string $filename, ?Throwable $previous = null)
    {
        $message = \sprintf('Malformed archive %s. Perhaps it is corrupted or was archived using an older API. Try recreating the archive.', $filename);
        parent::__construct($filename, $message, $previous);
    }
}
