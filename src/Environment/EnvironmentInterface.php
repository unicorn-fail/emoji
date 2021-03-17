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

namespace League\Emoji\Environment;

use League\Configuration\ConfigurationProviderInterface;
use League\Emoji\Dataset\RuntimeDataset;
use League\Emoji\Extension\ExtensionInterface;
use League\Emoji\Renderer\NodeRendererInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

interface EnvironmentInterface extends ConfigurationProviderInterface, EventDispatcherInterface, ListenerProviderInterface
{
    /**
     * @return ExtensionInterface[]
     */
    public function getExtensions(): iterable;

    /**
     * @psalm-param class-string|string $nodeClass
     *
     * @return iterable<NodeRendererInterface>
     */
    public function getRenderersForClass(string $nodeClass): iterable;

    public function getRuntimeDataset(string $index = 'hexcode'): RuntimeDataset;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void;
}
