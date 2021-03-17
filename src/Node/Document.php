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

namespace League\Emoji\Node;

class Document
{
    /** @var array<int, Node> */
    protected $nodes = [];

    public function appendNode(Node $node): void
    {
        $node->setDocument($this);

        $this->nodes[] = $node;
    }

    /**
     * @return Node[]
     */
    public function &getNodes(): array
    {
        return $this->nodes;
    }

    public function prependNode(Node $node): void
    {
        $node->setDocument($this);

        \array_unshift($this->nodes, $node);

        $this->nodes = \array_values($this->nodes);
    }

    public function replaceNode(Node $oldNode, ?Node $newNode = null): void
    {
        $index = \array_search($oldNode, $this->nodes, true);

        if ($index === false) {
            return;
        }

        $replacement = [];

        if ($newNode !== null) {
            $oldNode->setDocument();
            $newNode->setDocument($this);
            $replacement[] = $newNode;
        }

        \array_splice($this->nodes, /** @scrutinizer ignore-type */ $index, 1, $replacement);

        $this->nodes = \array_values($this->nodes);
    }
}
