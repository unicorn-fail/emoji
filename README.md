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

The `UnicornFail\Emoji\Converter` class provides a simple wrapper for converting emoticons, HTML entities and
shortcodes to proper unicode characters (emojis):

```php
use UnicornFail\Emoji\Converter;
use UnicornFail\Emoji\Emojibase\DatasetInterface;
use UnicornFail\Emoji\Emojibase\ShortcodeInterface;

// Default configuration.
$configuration = [
    'convertEmoticons'  => true,
    'exclude'           => [
        'shortcodes' => [],
    ],
    'locale'            => 'en',
    'native'            => null, // auto, true or false depending on locale set.
    'presentation'      => DatasetInterface::EMOJI,
    'preset'            => ShortcodeInterface::DEFAULT_PRESETS,
];

$converter = new Converter($configuration);

// Convert applicable values to unicodes (emojis).
echo $converter->convert('We <3 :unicorn: :D!');
// or
echo $converter->convertToUnicode('We <3 :unicorn: :D!');
// We ❤️ 🦄 😀!

// Convert applicable values to HTML entities.
echo  $converter->convertToHtml('We <3 :unicorn: :D!');
// We \&#x2764; \&#x1F984; \&#x1F600;!

// Convert applicable values to shortcodes.
echo  $converter->convertToShortcode('We <3 :unicorn: :D!');
// We :red-heart: :unicorn-face: :grinning-face:!
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

- [Mark Carver][@_markcarver]
- [Ben Sinclair] ([elvanto/litemoji])
- [Miles Johnson] ([milesj/emojibase])
- [All Contributors]

This code originally based on [elvanto/litemoji], maintained and copyrighted by [Ben Sinclair]. Currently, this project
still uses [milesj/emojibase] as its datasource, maintained and copyrighted by [Miles Johnson]. This project simply
wouldn't exist without either of their works!

## 📄 License

**unicorn-fail/emoji** is licensed under the BSD-3 license.  See the [`LICENSE`](LICENSE) file for more details.

## 🏛️ Governance

This project is primarily maintained by [Mark Carver][@_markcarver].

[Composer]: https://getcomposer.org/
[@_markcarver]: https://www.twitter.com/_markcarver
[All Contributors]: https://github.com/thephpleague/commonmark/contributors
[Ben Sinclair]: https://github.com/bensinclair
[elvanto/litemoji]: https://github.com/elvanto/litemoji
[Miles Johnson]: https://github.com/milesj
[milesj/emojibase]: https://github.com/milesj/emojibase
