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

namespace UnicornFail\Emoji\Renderer\Block;

use UnicornFail\Emoji\Node\Block\Document;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Renderer\ChildNodeRendererInterface;
use UnicornFail\Emoji\Renderer\NodeRendererInterface;

final class DocumentRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! ($node instanceof Document)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        return $childRenderer->renderNodes($node->children());
    }
}
