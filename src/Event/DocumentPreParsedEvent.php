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

use UnicornFail\Emoji\Input\InputInterface;
use UnicornFail\Emoji\Node\Document;

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

    /** @var InputInterface */
    private $input;

    public function __construct(Document $document, InputInterface $input)
    {
        $this->document = $document;
        $this->input    = $input;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getInput(): InputInterface
    {
        return $this->input;
    }

    public function replaceInput(InputInterface $input): void
    {
        $this->input = $input;
    }
}
