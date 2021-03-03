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

namespace UnicornFail\Emoji\Input;

use UnicornFail\Emoji\Exception\UnexpectedEncodingException;

class Input implements InputInterface
{
    /**
     * @var ?iterable<int, string>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $lines;

    /**
     * @var string
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $content;

    /**
     * @var ?int
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $lineCount;

    /**
     * @var int
     *
     * @psalm-readonly
     */
    private $lineOffset;

    public function __construct(string $content, int $lineOffset = 0)
    {
        if (! \mb_check_encoding($content, 'UTF-8')) {
            throw new UnexpectedEncodingException('Unexpected encoding - UTF-8 or ASCII was expected');
        }

        $this->content    = $content;
        $this->lineOffset = $lineOffset;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function getLines(): iterable
    {
        $this->splitLinesIfNeeded();

        foreach ($this->lines ?? [] as $i => $line) {
            yield $this->lineOffset + $i + 1 => $line;
        }
    }

    public function getLineCount(): int
    {
        $this->splitLinesIfNeeded();

        \assert($this->lineCount !== null);

        return $this->lineCount;
    }

    private function splitLinesIfNeeded(): void
    {
        if ($this->lines !== null) {
            return;
        }

        $lines = \preg_split('/\r\n|\n|\r/', $this->content);
        if ($lines === false) {
            throw new UnexpectedEncodingException('Failed to split content by line');
        }

        // Remove any newline which appears at the very end of the string.
        // We've already split the document by newlines, so we can simply drop
        // any empty element which appears on the end.
        if (\end($lines) === '') {
            \array_pop($lines);
        }

        $this->lines = $lines;

        $this->lineCount = \count($this->lines);
    }
}
