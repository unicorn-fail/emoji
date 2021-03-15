# unicorn-fail/emoji

> [![Latest Version](https://img.shields.io/packagist/v/unicorn-fail/emoji.svg?style=flat-square)](https://packagist.org/packages/unicorn-fail/emoji)
[![Total Downloads](https://img.shields.io/packagist/dt/unicorn-fail/emoji.svg?style=flat-square)](https://packagist.org/packages/unicorn-fail/emoji)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/unicorn-fail/emoji?style=flat-square)](https://packagist.org/packages/unicorn-fail/emoji)
[![Software License](https://img.shields.io/badge/License-BSD--3-blue.svg?style=flat-square)](LICENSE)<br>
[![Build Status](https://img.shields.io/github/workflow/status/unicorn-fail/emoji/Tests/latest.svg?style=flat-square)](https://github.com/unicorn-fail/emoji/actions?query=branch%3Alatest)
[![Scrutinizer coverage (GitHub/BitBucket)](https://img.shields.io/scrutinizer/coverage/g/unicorn-fail/emoji/latest?style=flat-square)](https://scrutinizer-ci.com/g/unicorn-fail/emoji/?branch=latest)
[![Scrutinizer code quality (GitHub/Bitbucket)](https://img.shields.io/scrutinizer/quality/g/unicorn-fail/emoji/latest?style=flat-square)](https://scrutinizer-ci.com/g/unicorn-fail/emoji/?branch=latest)
[![CII Best Practices Summary](https://img.shields.io/cii/summary/4286?style=flat-square)](https://bestpractices.coreinfrastructure.org/en/projects/4286)
[![Psalm coverage](https://shepherd.dev/github/unicorn-fail/emoji/coverage.svg)](https://shepherd.dev/github/unicorn-fail/emoji)
>
> **unicorn-fail/emoji** is a comprehensive PHP parser and converter of emoticons, HTML entities, shortcodes and
unicodes (emojis); utilizing [milesj/emojibase] as its data source.

## 📦 Installation & Basic Usage

This project requires PHP 7.2.5 or higher with the `mbstring` and `zlib` PHP extensions.
To install it via [Composer] simply run:

``` bash
$ composer require unicorn-fail/emoji
```

The `UnicornFail\Emoji\Emoji` class provides a simple wrapper for converting emoticons, HTML entities and
shortcodes to proper unicode characters (emojis):

```php
use UnicornFail\Emoji\EmojiConverter;
use UnicornFail\Emoji\Emojibase\EmojibaseDatasetInterface;
use UnicornFail\Emoji\Emojibase\EmojibaseShortcodeInterface;

$defaultConfiguration = [
    /** @var array<string, string> (see EmojiConverter::TYPES) */
    'convert' => [
        EmojiConverter::EMOTICON    => EmojiConverter::UNICODE,
        EmojiConverter::HTML_ENTITY => EmojiConverter::UNICODE,
        EmojiConverter::SHORTCODE   => EmojiConverter::UNICODE,
        EmojiConverter::UNICODE     => EmojiConverter::UNICODE,
    ],

    /** @var array<string, mixed> */
    'exclude' => [
        /** @var string[] */
        'shortcodes' => [],
    ],

    /** @var string */
    'locale' => 'en',

    /** @var ?bool */
    'native' => null, // Auto (null), becomes true or false depending on locale set.

    /** @var int */
    'presentation' => EmojibaseDatasetInterface::EMOJI,

    /** @var string[] */
    'preset' => EmojibaseShortcodeInterface::DEFAULT_PRESETS,
];

// Convert all applicable values to unicode emojis (default configuration).
$converter = EmojiConverter::create();
echo $converter->convert('We <3 :unicorn: :D!');
// We ❤️ 🦄 😀!

// Convert all applicable values to HTML entities.
$converter = EmojiConverter::create(['convert' => EmojiConverter::HTML_ENTITY]);
echo  $converter->convert('We <3 :unicorn: :D!');
// We \&#x2764; \&#x1F984; \&#x1F600;!

// Convert all applicable values to shortcodes.
$converter = EmojiConverter::create(['convert' => EmojiConverter::SHORTCODE]);
echo  $converter->convert('We <3 :unicorn: :D!');
// We :heart: :unicorn: :grinning:!
```

Please note that only UTF-8 and ASCII encodings are supported.  If your content uses a different encoding please
convert it to UTF-8 before running it through this library.

## 📓 Documentation

@todo

## ⏫ Upgrading

@todo

## 🏷️ Versioning

[SemVer](http://semver.org/) is followed closely. Minor and patch releases should not introduce breaking changes
to the codebase; however, they might change the resulting AST or HTML output of parsed Markdown (due to bug fixes,
spec changes, etc.)  As a result, you might get slightly different HTML, but any custom code built onto this library
should still function correctly.

Any classes or methods marked `@internal` are not intended for use outside of this library and are subject to breaking
changes at any time, so please avoid using them.

## 🛠️ Maintenance & Support

@todo

## 👷‍♀️ Contributing

@todo

## 🧪 Testing

``` bash
$ composer test
```

Or, to include coverage support:
```bash
$ composer test-coverage
```

## 👥 Credits & Acknowledgements

- [Mark Halliwell][@markehalliwell]
- [Ben Sinclair] ([elvanto/litemoji])
- [Miles Johnson] ([milesj/emojibase])
- [All Contributors]

This code originally based on [elvanto/litemoji], maintained and copyrighted by [Ben Sinclair]. Currently, this project
still uses [milesj/emojibase] as its datasource, maintained and copyrighted by [Miles Johnson]. This project simply
wouldn't exist without either of their works!

## 📄 License

**unicorn-fail/emoji** is licensed under the BSD-3 license.  See the [`LICENSE`](LICENSE) file for more details.

## 🏛️ Governance

This project is primarily maintained by [Mark Halliwell][@markehalliwell].

[Composer]: https://getcomposer.org/
[@markehalliwell]: https://www.twitter.com/markehalliwell
[All Contributors]: https://github.com/thephpleague/commonmark/contributors
[Ben Sinclair]: https://github.com/bensinclair
[elvanto/litemoji]: https://github.com/elvanto/litemoji
[Miles Johnson]: https://github.com/milesj
[milesj/emojibase]: https://github.com/milesj/emojibase
