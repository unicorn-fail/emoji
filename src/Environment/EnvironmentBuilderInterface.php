<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji\Environment;

use League\Emoji\Extension\ExtensionInterface;
use League\Emoji\Renderer\NodeRendererInterface;

/**
 * Interface for building the Environment with any extensions, parsers, listeners, etc. that it may need
 */
interface EnvironmentBuilderInterface
{
    /**
     * Registers the given event listener
     *
     * @param string   $eventClass Fully-qualified class name of the event this listener should respond to
     * @param callable $listener   Listener to be executed
     * @param int      $priority   Priority (a higher number will be executed earlier)
     *
     * @return static
     */
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0): EnvironmentBuilderInterface;

    /**
     * Registers the given extension with the Environment
     *
     * @return static
     */
    public function addExtension(ExtensionInterface $extension): EnvironmentBuilderInterface;

    /**
     * Registers the given node renderer with the Environment
     *
     * @param string                $nodeClass The fully-qualified node element class name the renderer below should handle
     * @param NodeRendererInterface $renderer  The renderer responsible for rendering the type of element given above
     * @param int                   $priority  Priority (a higher number will be executed earlier)
     *
     * @return static
     */
    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0): EnvironmentBuilderInterface;
}
