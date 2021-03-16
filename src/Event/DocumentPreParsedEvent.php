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

namespace League\Emoji\Event;

use League\Emoji\Node\Document;

/**
 * Event dispatched when the document is about to be parsed
 */
final class DocumentPreParsedEvent extends AbstractEvent
{
    /**
     * @var Document
     *
     * @psalm-readonly
     */
    private $document;

    /** @var string */
    private $input;

    public function __construct(Document $document, string $input)
    {
        $this->document = $document;
        $this->input    = $input;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function replaceInput(string $input): void
    {
        $this->input = $input;
    }
}
