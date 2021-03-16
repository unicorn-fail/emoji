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

namespace League\Emoji\Event;

final class DocumentRenderedEvent extends AbstractEvent
{
    /** @var string */
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @psalm-mutation-free
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @psalm-external-mutation-free
     */
    public function replaceContent(string $content): void
    {
        $this->content = $content;
    }
}
