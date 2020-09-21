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

namespace UnicornFail\Emoji\Renderer\Inline;

use UnicornFail\Emoji\Node\Inline\Text;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Renderer\ChildNodeRendererInterface;
use UnicornFail\Emoji\Renderer\NodeRendererInterface;

final class TextRenderer implements NodeRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! ($node instanceof Text)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        return $node;
    }
}
