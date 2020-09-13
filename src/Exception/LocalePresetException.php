<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Exception;

class LocalePresetException extends \RuntimeException implements EmojiException
{
    /**
     * @param \Throwable[] $throwables
     */
    public function __construct(string $locale, array $throwables = [])
    {
        $reasons = [];
        foreach ($throwables as $preset => $throwable) {
            $reasons[] = \sprintf('%s: %s', $preset, $throwable->getMessage());
        }

        parent::__construct(\sprintf(
            "Attempted to load the locale \"%s\" dataset. However, the following preset(s) were unable to be loaded:\n%s",
            $locale,
            \implode("\n", $reasons)
        ));
    }
}
