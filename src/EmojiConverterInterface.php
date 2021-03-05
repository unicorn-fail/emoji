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

namespace UnicornFail\Emoji;

use UnicornFail\Emoji\Output\RenderedContentInterface;
use UnicornFail\Emoji\Parser\Lexer;

/**
 * Interface for a service which converts emojis.
 */
interface EmojiConverterInterface
{
    public const EMOTICON    = 'emoticon';
    public const HTML_ENTITY = 'html_entity';
    public const SHORTCODE   = 'shortcode';
    public const UNICODE     = 'unicode';

    public const TYPES = [
        Lexer::T_EMOTICON => self::EMOTICON,
        Lexer::T_HTML_ENTITY => self::HTML_ENTITY,
        Lexer::T_SHORTCODE => self::SHORTCODE,
        Lexer::T_UNICODE => self::UNICODE,
    ];

    public function convert(string $input): RenderedContentInterface;
}
