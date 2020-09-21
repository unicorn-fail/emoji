<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Environment;

use UnicornFail\Emoji\Renderer\NodeRendererInterface;

interface RenderableEnvironmentInterface
{
    /**
     * Registers the given node renderer with the Environment
     *
     * @param string                $nodeClass The fully-qualified node element class name the renderer below should handle
     * @param NodeRendererInterface $renderer  The renderer responsible for rendering the type of element given above
     * @param int                   $priority  Priority (a higher number will be executed earlier)
     *
     * @return static
     */
    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0);

    /**
     * @psalm-param class-string $nodeClass
     *
     * @return iterable<NodeRendererInterface>
     */
    public function getRenderersForClass(string $nodeClass): iterable;
}
