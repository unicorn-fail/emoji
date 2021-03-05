<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Emojibase;

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
interface EmojibaseShortcodeInterface
{
    public const DEFAULT_PRESETS = [
        self::PRESET_EMOJIBASE,
        self::PRESET_CLDR_NATIVE,
        self::PRESET_CLDR,
    ];

    public const PRESETS = [
        self::PRESET_CLDR             => self::PRESET_CLDR,
        self::PRESET_CLDR_NATIVE      => self::PRESET_CLDR_NATIVE,
        self::PRESET_EMOJIBASE        => self::PRESET_EMOJIBASE,
        self::PRESET_EMOJIBASE_LEGACY => self::PRESET_EMOJIBASE_LEGACY,
        self::PRESET_GITHUB           => self::PRESET_GITHUB,
        self::PRESET_IAMCAL           => self::PRESET_IAMCAL,
        self::PRESET_JOYPIXELS        => self::PRESET_JOYPIXELS,
    ];

    public const PRESET_ALIASES = [
        self::PRESET_DISCORD => self::PRESET_JOYPIXELS,
        self::PRESET_SLACK   => self::PRESET_IAMCAL,
    ];

    public const PRESET_CLDR = 'cldr';

    public const PRESET_CLDR_NATIVE = 'cldr-native';

    public const PRESET_DISCORD = 'discord';

    public const PRESET_EMOJIBASE = 'emojibase';

    public const PRESET_EMOJIBASE_LEGACY = 'emojibase-legacy';

    public const PRESET_GITHUB = 'github';

    public const PRESET_IAMCAL = 'iamcal';

    public const PRESET_JOYPIXELS = 'joypixels';

    public const PRESET_SLACK = 'slack';

    public const SUPPORTED_PRESETS = [
        self::PRESET_CLDR,
        self::PRESET_CLDR_NATIVE,
        self::PRESET_EMOJIBASE,
        self::PRESET_EMOJIBASE_LEGACY,
        self::PRESET_GITHUB,
        self::PRESET_IAMCAL,
        self::PRESET_JOYPIXELS,
        self::PRESET_DISCORD,
        self::PRESET_SLACK,
    ];
}
