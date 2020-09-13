<?php

declare(strict_types=1);

namespace UnicornFail\Emoji;

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
interface EmojibaseInterface
{
    public const AUTO = null;

    public const EMOJI = 1;

    public const EMOJI_VERSIONS = [
        '1.0',
        '2.0',
        '3.0',
        '4.0',
        '5.0',
        '11.0',
        '12.0',
        '12.1',
        '13.0',
    ];

    public const FEMALE = 0;

    public const FIRST_UNICODE_EMOJI_VERSION = '6.0.0';

    public const GENDER = [
        self::FEMALE => 'female',
        self::MALE   => 'male',
    ];

    public const LATEST_CLDR_VERSION = '37';

    public const LATEST_EMOJI_VERSION = '13.0';

    public const LATEST_UNICODE_VERSION = '13.0.0';

    public const MALE = 1;

    public const NON_LATIN_LOCALES = [
        'ja',
        'ko',
        'ru',
        'th',
        'uk',
        'zh',
        'zh-hant',
    ];

    public const SEQUENCE_REMOVAL_PATTERN = '/200D|FE0E|FE0F/g';

    public const SUPPORTED_LOCALES = [
        'da',
        'de',
        'en',
        'en-gb',
        'es',
        'es-mx',
        'et',
        'fi',
        'fr',
        'hu',
        'it',
        'ja',
        'ko',
        'lt',
        'ms',
        'nb',
        'nl',
        'pl',
        'pt',
        'ru',
        'sv',
        'th',
        'uk',
        'zh',
        'zh-hant',
    ];

    public const SUPPORTED_PRESENTATIONS = [
        self::AUTO,
        self::TEXT,
        self::EMOJI,
    ];

    public const TEXT = 0;

    public const UNICODE_VERSIONS = [
        '6.0',
        '6.1',
        '6.2',
        '6.3',
        '7.0',
        '8.0',
        '9.0',
        '10.0',
        '11.0',
        '12.0',
        '12.1',
        '13.0',
    ];
}
