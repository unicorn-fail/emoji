<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Token\AbstractToken;

interface ParserInterface extends EmojibaseRegexInterface
{
    public const T_TEXT = 0;

    public const T_EMOTICON = 1;

    public const T_HTML_ENTITY = 2;

    public const T_SHORTCODE = 3;

    public const T_UNICODE = 4;

    public const INDICES = ['emoji', 'emoticon', 'htmlEntity', 'shortcodes', 'text'];

    /**
     * @return AbstractToken[]
     */
    public function parse(string $input): array;
}
