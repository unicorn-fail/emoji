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

namespace League\Emoji\Node;

use Dflydev\DotAccessData\Data;

abstract class Node extends Data implements \Stringable
{
    /**
     * @var Document|null
     *
     * @psalm-readonly-allow-private-mutation
     */
    protected $document;

    /** @var string */
    protected $content = '';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $content = '', array $data = [])
    {
        parent::__construct(['attributes' => new Data()]);
        $this->content = $content;

        $this->import($data);
    }

    public function __clone()
    {
        $this->document = null;
    }

    public function __toString(): string
    {
        return $this->getContent();
    }

    public function addClass(string ...$classes): void
    {
        $class = (string) $this->getAttribute('class', '');

        foreach ($classes as $value) {
            if ($class !== '') {
                $class .= ' ';
            }

            $class .= $value;
        }

        if ($class) {
            $this->setAttribute('class', $class);
        }
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->getAttributes()->get($name, $default);
    }

    public function getAttributes(): Data
    {
        /** @var Data $attributes */
        $attributes = $this->get('attributes');

        return $attributes;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function hasAttribute(string $name): bool
    {
        return $this->getAttributes()->has($name);
    }

    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): void
    {
        $this->getAttributes()->set($name, $value);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function setAttributes(array $attributes = []): void
    {
        $this->set('attributes', new Data($attributes));
    }

    public function setContent(string $contents): void
    {
        $this->content = $contents;
    }

    public function setDocument(?Document $document = null): void
    {
        $this->document = $document;
    }

    public function replaceWith(Node $replacement): void
    {
        if ($this->document) {
            $this->document->replaceNode($this, $replacement);
        }
    }
}
