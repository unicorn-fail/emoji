<?php

declare(strict_types=1);

namespace League\Emoji\Exception;

class RenderNodeException extends \RuntimeException implements EmojiException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
