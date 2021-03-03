<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Traits;

use UnicornFail\Emoji\Renderer\NodeRendererInterface;
use UnicornFail\Emoji\Util\PrioritizedList;

trait RenderableEnvironmentTrait
{
    /**
     * @var array<string, PrioritizedList<NodeRendererInterface>>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $renderersByClass = [];

    /**
     * {@inheritDoc}
     */
    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0)
    {
        $this->assertUninitialized('Failed to add renderer.');

        if (! isset($this->renderersByClass[$nodeClass])) {
            /** @var PrioritizedList<NodeRendererInterface> $renderers */
            $renderers = new PrioritizedList();

            $this->renderersByClass[$nodeClass] = $renderers;
        }

        $this->renderersByClass[$nodeClass]->add($renderer, $priority);
        $this->injectEnvironmentAndConfigurationIfNeeded($renderer);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return PrioritizedList<NodeRendererInterface>
     */
    public function getRenderersForClass(string $nodeClass): iterable
    {
        $this->initialize();

        // If renderers are defined for this specific class, return them immediately
        if (isset($this->renderersByClass[$nodeClass])) {
            return $this->renderersByClass[$nodeClass];
        }

        while (\class_exists($parent = (string) ($parent ?? $nodeClass)) && ($parent = \get_parent_class($parent))) {
            if (! isset($this->renderersByClass[$parent])) {
                continue;
            }

            // "Cache" this result to avoid future loops
            return $this->renderersByClass[$nodeClass] = $this->renderersByClass[$parent];
        }

        /** @var PrioritizedList<NodeRendererInterface> $renderers */
        $renderers = new PrioritizedList();

        return $renderers;
    }
}
