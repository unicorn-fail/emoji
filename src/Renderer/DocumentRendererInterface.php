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

namespace UnicornFail\Emoji\Renderer;

use UnicornFail\Emoji\Node\Document;

/**
 * Renders a parsed Document AST to rendered content (string).
 */
interface DocumentRendererInterface
{
    /**
     * Renders the given Document node.
     */
    public function renderDocument(Document $document): string;
}
