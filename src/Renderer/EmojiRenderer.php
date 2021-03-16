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

namespace League\Emoji\Renderer;

use League\Emoji\Node\Emoji;
use League\Emoji\Node\Node;

final class EmojiRenderer implements NodeRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(Node $node)
    {
        if (! ($node instanceof Emoji)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        return $node;
    }
}
