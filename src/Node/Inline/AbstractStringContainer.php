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

namespace UnicornFail\Emoji\Node\Inline;

use UnicornFail\Emoji\Node\StringContainerInterface;

class AbstractStringContainer extends AbstractInline implements \Stringable, StringContainerInterface
{
    /** @var string */
    protected $literal = '';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $contents = '', array $data = [])
    {
        $this->literal = $contents;
        $this->data    = $data;
    }

    public function __toString(): string
    {
        return $this->getLiteral();
    }

    public function getLiteral(): string
    {
        return $this->literal;
    }

    public function setLiteral(string $contents): void
    {
        $this->literal = $contents;
    }
}
