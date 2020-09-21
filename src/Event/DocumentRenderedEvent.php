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

namespace UnicornFail\Emoji\Event;

use UnicornFail\Emoji\Output\RenderedContentInterface;

final class DocumentRenderedEvent extends AbstractEvent
{
    /** @var RenderedContentInterface */
    private $content;

    public function __construct(RenderedContentInterface $content)
    {
        $this->content = $content;
    }

    /**
     * @psalm-mutation-free
     */
    public function getContent(): RenderedContentInterface
    {
        return $this->content;
    }

    /**
     * @psalm-external-mutation-free
     */
    public function replaceContent(RenderedContentInterface $content): void
    {
        $this->content = $content;
    }
}
