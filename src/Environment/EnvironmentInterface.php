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

namespace UnicornFail\Emoji\Environment;

use League\Configuration\ConfigurationProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use UnicornFail\Emoji\Dataset\RuntimeDataset;
use UnicornFail\Emoji\Extension\ExtensionInterface;
use UnicornFail\Emoji\Renderer\NodeRendererInterface;

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
