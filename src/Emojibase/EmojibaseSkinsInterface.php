<?php

declare(strict_types=1);

namespace League\Emoji\Emojibase;

/*!
 * IMPORTANT NOTE!
 *
 * THIS FILE IS BASED ON EXTRACTED DATA FROM THE NPM MODULE:
 *
 *     https://www.npmjs.com/package/emojibase
 *
 * DO NOT ATTEMPT TO DIRECTLY MODIFY THIS FILE. ALL MANUAL CHANGES MADE TO THIS FILE
 * WILL BE DESTROYED AUTOMATICALLY THE NEXT TIME IT IS REBUILT.
 */
interface EmojibaseSkinsInterface
{
    public const DARK_SKIN = 5;

    public const LIGHT_SKIN = 1;

    public const MEDIUM_DARK_SKIN = 4;

    public const MEDIUM_LIGHT_SKIN = 2;

    public const MEDIUM_SKIN = 3;

    public const SKIN_KEY_DARK = 'dark';

    public const SKIN_KEY_LIGHT = 'light';

    public const SKIN_KEY_MEDIUM = 'medium';

    public const SKIN_KEY_MEDIUM_DARK = 'medium-dark';

    public const SKIN_KEY_MEDIUM_LIGHT = 'medium-light';

    public const SKIN_TONES = [
        self::LIGHT_SKIN        => self::SKIN_KEY_LIGHT,
        self::MEDIUM_LIGHT_SKIN => self::SKIN_KEY_MEDIUM_LIGHT,
        self::MEDIUM_SKIN       => self::SKIN_KEY_MEDIUM,
        self::MEDIUM_DARK_SKIN  => self::SKIN_KEY_MEDIUM_DARK,
        self::DARK_SKIN         => self::SKIN_KEY_DARK,
    ];
}
