<?php

declare(strict_types=1);

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UnicornFail\Emoji\Event;

use UnicornFail\Emoji\Node\Block\Document;

/**
 * Event dispatched when the document has been fully parsed
 */
final class DocumentParsedEvent extends AbstractEvent
{
    /**
     * @var Document
     *
     * @psalm-readonly
     */
    private $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }
}
