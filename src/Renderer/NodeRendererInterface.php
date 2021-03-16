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

namespace League\Emoji\Renderer;

use League\Emoji\Node\Node;

interface NodeRendererInterface
{
    /**
     * @return \Stringable|string|null
     */
    public function render(Node $node);
}
