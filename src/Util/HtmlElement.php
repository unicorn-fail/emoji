<?php

declare(strict_types=1);

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji\Util;

class HtmlElement implements \Stringable
{
    public const CSS_IDENTIFIER_FILTERS = [
        ' ' => '-',
        '_' => '-',
        '/' => '-',
        '[' => '-',
        ']' => '',
    ];

    /** @var string */
    protected $tagName;

    /** @var array<string, bool|string> */
    protected $attributes = [];

    /** @var HtmlElement|HtmlElement[]|string */
    protected $contents;

    /** @var bool */
    protected $selfClosing = false;

    /**
     * @param string                                $tagName     Name of the HTML tag
     * @param array<string, bool|string>            $attributes  Array of attributes (values should be unescaped)
     * @param HtmlElement|HtmlElement[]|string|null $contents    Inner contents, pre-escaped if needed
     * @param bool                                  $selfClosing Whether the tag is self-closing
     */
    public function __construct(string $tagName, array $attributes = [], $contents = '', bool $selfClosing = false)
    {
        $this->tagName     = $tagName;
        $this->selfClosing = $selfClosing;

        /**
         * @var bool|string $value
         */
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }

        $this->setContents($contents ?? '');
    }

    /**
     * Prepares a string for use as a CSS identifier (element, class, or ID name).
     *
     * Link below shows the syntax for valid CSS identifiers (including element
     * names, classes, and IDs in selectors).
     *
     * @see http://www.w3.org/TR/CSS21/syndata.html#characters
     *
     * @param string   $identifier
     *   The identifier to clean.
     * @param string[] $filter
     *   An array of string replacements to use on the identifier.
     *
     * @return string
     *   The cleaned identifier.
     *
     * Note: shamelessly copied from
     * https://github.com/drupal/core-utility/blob/6807795c25836ccdb3f50d4396c4427705b7b6ad/Html.php
     */
    public static function cleanCssIdentifier(string $identifier, array $filter = self::CSS_IDENTIFIER_FILTERS): string
    {
        // We could also use strtr() here but its much slower than str_replace(). In
        // order to keep '__' to stay '__' we first replace it with a different
        // placeholder after checking that it is not defined as a filter.
        $doubleUnderscoreReplacements = 0;
        if (! isset($filter['__'])) {
            $identifier = \str_replace('__', '##', $identifier, $doubleUnderscoreReplacements);
        }

        $identifier = \str_replace(\array_keys($filter), \array_values($filter), $identifier);
        // Replace temporary placeholder '##' with '__' only if the original
        // $identifier contained '__'.
        if ($doubleUnderscoreReplacements > 0) {
            $identifier = \str_replace('##', '__', $identifier);
        }

        // Valid characters in a CSS identifier are:
        // - the hyphen (U+002D)
        // - a-z (U+0030 - U+0039)
        // - A-Z (U+0041 - U+005A)
        // - the underscore (U+005F)
        // - 0-9 (U+0061 - U+007A)
        // - ISO 10646 characters U+00A1 and higher
        // We strip out any character not in the above list.
        $identifier = (string) \preg_replace('/[^\x{002D}\x{0030}-\x{0039}\x{0041}-\x{005A}\x{005F}\x{0061}-\x{007A}\x{00A1}-\x{FFFF}]/u', '', $identifier);

        // Identifiers cannot start with a digit, two hyphens, or a hyphen followed by a digit.
        $identifier = (string) \preg_replace(['/^[0-9]/', '/^(-[0-9])|^(--)/'], ['_', '__'], $identifier);

        return $identifier;
    }

    public function addClass(string ...$classes): self
    {
        // Merge classes into existing classes.
        $existing = \explode(' ', (string) ($this->attributes['class'] ?? ''));
        $classes  = \array_merge($existing, $classes);

        // Split any strings that may have multiple classes in them.
        $classes = \array_map(static function (string $class) {
            return \explode(' ', $class);
        }, $classes);

        // Flatten classes back into a single level array.
        $classes = \array_reduce(
            $classes,
            /**
             * @param string[] $a
             * @param string[] $v
             *
             * @return string[]
             */
            static function (array $a, array $v): array {
                return \array_merge($a, $v);
            },
            []
        );

        // Filter out empty items and ensure classes are unique.
        $classes = \array_filter(\array_unique($classes));

        // Remove trailing spaces and normalize the class.
        $classes = \array_map(static function (string $class): string {
            return self::cleanCssIdentifier(\trim($class));
        }, $classes);

        // Convert the array of classes back into a string.
        $this->attributes['class'] = \trim(\implode(' ', $classes));

        return $this;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * @return array<string, bool|string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttributesAsString(): string
    {
        $attributes = '';
        foreach ($this->attributes as $key => $value) {
            if ($value === true) {
                $attributes .= ' ' . $key;
            } elseif ($value === false) {
                continue;
            } else {
                $attributes .= ' ' . $key . '="' . Xml::escape($value) . '"';
            }
        }

        return $attributes;
    }

    /**
     * @return bool|string|null
     */
    public function getAttribute(string $key)
    {
        if (! isset($this->attributes[$key])) {
            return null;
        }

        return $this->attributes[$key];
    }

    /**
     * @param string|string[]|bool $value
     */
    public function setAttribute(string $key, $value): self
    {
        if ($key === 'class') {
            $this->attributes['class'] = '';
            if (! \is_array($value)) {
                $value = [$value];
            }

            /** @var string[] $value */
            return $this->addClass(...$value);
        }

        if (\is_array($value)) {
            $this->attributes[$key] = \implode(' ', \array_unique($value));
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @return HtmlElement|HtmlElement[]|string
     */
    public function getContents(bool $asString = true)
    {
        if (! $asString) {
            return $this->contents;
        }

        return $this->getContentsAsString();
    }

    /**
     * Sets the inner contents of the tag (must be pre-escaped if needed)
     *
     * @param HtmlElement|HtmlElement[]|string|null $contents
     */
    public function setContents($contents): self
    {
        $this->contents = $contents ?? '';

        return $this;
    }

    public function __toString(): string
    {
        $result = '<' . $this->tagName . $this->getAttributesAsString();

        if ($this->contents !== '') {
            $result .= '>' . $this->getContentsAsString() . '</' . $this->tagName . '>';
        } elseif ($this->selfClosing && $this->tagName === 'input') {
            $result .= '>';
        } elseif ($this->selfClosing) {
            $result .= ' />';
        } else {
            $result .= '></' . $this->tagName . '>';
        }

        return $result;
    }

    private function getContentsAsString(): string
    {
        if (\is_string($this->contents)) {
            return $this->contents;
        }

        if (\is_array($this->contents)) {
            return \implode('', $this->contents);
        }

        return (string) $this->contents;
    }
}
