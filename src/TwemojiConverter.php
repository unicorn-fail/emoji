<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Environment\Environment;
use UnicornFail\Emoji\Extension\Twemoji\TwemojiExtension;

final class TwemojiConverter extends EmojiConverter
{
    public const CONVERSION_TYPE = TwemojiExtension::CONVERSION_TYPE;

    /**
     * @param array<string, mixed> $config
     */
    public static function create(array $config = [], bool $setAsDefaultConversionType = true): EmojiConverterInterface
    {
        $environment = Environment::create($config);
        $environment->addExtension(new TwemojiExtension($setAsDefaultConversionType));

        return new self($environment);
    }
}
