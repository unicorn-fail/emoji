<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji;

use League\Emoji\Lexer\EmojiLexer;

/**
 * Interface for a service which converts emojis.
 */
interface EmojiConverterInterface
{
    public const EMOTICON    = 'emoticon';
    public const HTML_ENTITY = 'htmlEntity';
    public const SHORTCODE   = 'shortcode';
    public const UNICODE     = 'unicode';

    public const TYPES = [
        EmojiLexer::T_EMOTICON    => self::EMOTICON,
        EmojiLexer::T_HTML_ENTITY => self::HTML_ENTITY,
        EmojiLexer::T_SHORTCODE   => self::SHORTCODE,
        EmojiLexer::T_UNICODE     => self::UNICODE,
    ];

    public function convert(string $input): string;
}
