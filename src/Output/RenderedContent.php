<?php

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace UnicornFail\Emoji\Output;

use UnicornFail\Emoji\Node\Block\Document;

class RenderedContent implements RenderedContentInterface
{
    /**
     * @var Document
     *
     * @psalm-readonly
     */
    private $document;

    /**
     * @var string
     *
     * @psalm-readonly
     */
    private $content;

    public function __construct(Document $document, string $content)
    {
        $this->document = $document;
        $this->content  = $content;
    }

    /**
     * @psalm-mutation-free
     */
    public function __toString(): string
    {
        return $this->getContent();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }
}
