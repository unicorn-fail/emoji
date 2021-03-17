<?php

declare(strict_types=1);

namespace League\Emoji\Environment;

use League\Configuration\Configuration;
use League\Configuration\ConfigurationInterface;
use League\Emoji\Dataset\RuntimeDataset;
use League\Emoji\Event\ListenerData;
use League\Emoji\Extension\ExtensionInterface;
use League\Emoji\Renderer\NodeRendererInterface;
use League\Emoji\Util\PrioritizedList;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;

abstract class AbstractEnvironment implements EnvironmentInterface
{
    /** @var Configuration */
    protected $config;

    /** @var ?RuntimeDataset */
    protected $dataset;

    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    protected $extensions = [];

    /** @var ?EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @var bool
     *
     * @psalm-readonly-allow-private-mutation
     */
    protected $initialized = false;

    /**
     * @var ?PrioritizedList<ListenerData>
     *
     * @psalm-readonly-allow-private-mutation
     */
    protected $listenerData;

    /**
     * @var array<string, PrioritizedList<NodeRendererInterface>>
     *
     * @psalm-readonly-allow-private-mutation
     */
    protected $renderersByClass = [];

    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    protected $uninitializedExtensions = [];

    /**
     * @throws \RuntimeException
     */
    protected function assertUninitialized(string $message): void
    {
        if ($this->initialized) {
            throw new \RuntimeException(\sprintf('%s The Environment has already been initialized.', $message));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event)
    {
        $this->initialize();

        if ($this->eventDispatcher !== null) {
            return $this->eventDispatcher->dispatch($event);
        }

        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listener($event);
        }

        return $event;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        $this->initializeConfiguration();

        return $this->config->reader();
    }

    /**
     * {@inheritDoc}
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions(): iterable
    {
        return $this->extensions;
    }

    /**
     * {@inheritDoc}
     *
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($this->listenerData === null) {
            /** @var PrioritizedList<ListenerData> $listenerData */
            $listenerData       = new PrioritizedList();
            $this->listenerData = $listenerData;
        }

        /** @var ListenerData $listenerData */
        foreach ($this->listenerData as $listenerData) {
            if (! \is_a($event, $listenerData->getEvent())) {
                continue;
            }

            yield function (object $event) use ($listenerData): void {
                $this->initialize();

                \call_user_func($listenerData->getListener(), $event);
            };
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderersForClass(string $nodeClass): iterable
    {
        $this->initialize();

        // If renderers are defined for this specific class, return them immediately
        if (isset($this->renderersByClass[$nodeClass])) {
            return $this->renderersByClass[$nodeClass];
        }

        while (\class_exists($parent = $parent ?? $nodeClass) && ($parent = \get_parent_class($parent))) {
            if (! isset($this->renderersByClass[$parent])) {
                continue;
            }

            // "Cache" this result to avoid future loops
            return $this->renderersByClass[$nodeClass] = $this->renderersByClass[$parent];
        }

        return [];
    }

    public function getRuntimeDataset(string $index = 'hexcode'): RuntimeDataset
    {
        $this->initialize();

        if ($this->dataset === null) {
            $this->dataset = new RuntimeDataset($this->getConfiguration());
        }

        return $this->dataset->indexBy($index);
    }

    protected function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initializeConfiguration();

        $this->initializeExtensions();

        $this->initialized = true;
    }

    abstract protected function initializeConfiguration(): void;

    abstract protected function initializeExtensions(): void;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }
}
