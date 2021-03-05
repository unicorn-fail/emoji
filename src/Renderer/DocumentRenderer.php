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

namespace UnicornFail\Emoji\Renderer;

use League\Configuration\ConfigurationAwareInterface;
use UnicornFail\Emoji\Environment\EnvironmentAwareInterface;
use UnicornFail\Emoji\Environment\EnvironmentInterface;
use UnicornFail\Emoji\Event\DocumentRenderedEvent;
use UnicornFail\Emoji\Node\Document;
use UnicornFail\Emoji\Node\Node;
use UnicornFail\Emoji\Output\RenderedContent;
use UnicornFail\Emoji\Output\RenderedContentInterface;

final class DocumentRenderer implements DocumentRendererInterface
{
    /** @var EnvironmentInterface */
    private $environment;

    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    public function renderDocument(Document $document): RenderedContentInterface
    {
        $output = new RenderedContent($document, (string) $this->renderNodes($document->children()));

        $event = new DocumentRenderedEvent($output);
        $this->environment->dispatch($event);

        return $event->getContent();
    }

    /**
     * @param Node[] $nodes
     */
    protected function renderNodes(iterable $nodes): string
    {
        $output = '';

        foreach ($nodes as $node) {
            $output .= $this->renderNode($node);
        }

        return $output;
    }

    /**
     * @return \Stringable|string
     *
     * @throws \RuntimeException
     */
    private function renderNode(Node $node)
    {
        $renderers = $this->environment->getRenderersForClass(\get_class($node));

        /** @var NodeRendererInterface $renderer */
        foreach ($renderers as $renderer) {
            if ($renderer instanceof ConfigurationAwareInterface) {
                $renderer->setConfiguration($this->environment->getConfiguration());
            }

            if ($renderer instanceof EnvironmentAwareInterface) {
                $renderer->setEnvironment($this->environment);
            }

            if (($result = $renderer->render($node)) !== null) {
                return $result;
            }
        }

        throw new \RuntimeException('Unable to find corresponding renderer for node type ' . \get_class($node));
    }
}
