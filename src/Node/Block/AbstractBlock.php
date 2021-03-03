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

namespace UnicornFail\Emoji\Node\Block;

use UnicornFail\Emoji\Node\Node;

/**
 * Block-level element
 *
 * @method parent() ?AbstractBlock
 */
abstract class AbstractBlock extends Node
{
    /**
     * Used for storage of arbitrary data.
     *
     * @var array<string, mixed>
     */
    public $data = [];

    /** @var int|null */
    protected $startLine;

    /** @var int|null */
    protected $endLine;

    protected function setParent(?Node $node = null): void
    {
        if ($node && ! $node instanceof self) {
            throw new \InvalidArgumentException('Parent of block must also be block (cannot be inline)');
        }

        parent::setParent($node);
    }

    public function setStartLine(?int $startLine): void
    {
        $this->startLine = $startLine;
        if ($this->endLine === null) {
            $this->endLine = $startLine;
        }
    }

    public function getStartLine(): ?int
    {
        return $this->startLine;
    }

    public function setEndLine(?int $endLine): void
    {
        $this->endLine = $endLine;
    }

    public function getEndLine(): ?int
    {
        return $this->endLine;
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getData(string $key, $default = null)
    {
        return \array_key_exists($key, $this->data)
            ? $this->data[$key]
            : $default;
    }
}
