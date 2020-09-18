<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Token;

interface TokenInterface extends \Stringable
{
    /**
     * @param TokenInterface[]              $tokens
     * @param callable(TokenInterface):bool $callback
     *
     * @return static[]
     */
    public static function filter(array $tokens, ?callable $callback = null): array;

    public function getValue(): string;
}
