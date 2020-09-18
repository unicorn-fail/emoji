<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Token;

abstract class AbstractToken implements \Stringable
{
    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param AbstractToken[]              $tokens
     * @param callable(AbstractToken):bool $callback
     *
     * @return static[]
     */
    public static function filter(array $tokens, ?callable $callback = null): array
    {
        if (! $callback) {
            /** @var callable(AbstractToken):bool $callback */
            $callback = static function (AbstractToken $token): bool {
                return $token instanceof static;
            };
        }

        /** @var static[] $tokens */
        $tokens = \array_filter($tokens, $callback);

        return $tokens;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
