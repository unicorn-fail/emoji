<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Traits;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use UnicornFail\Emoji\Event\ListenerData;
use UnicornFail\Emoji\Util\PrioritizedList;

trait ListeningEnvironmentTrait
{
    /**
     * @var ?PrioritizedList<ListenerData>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $listenerData;

    /** @var ?EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * {@inheritDoc}
     */
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0)
    {
        $this->assertUninitialized('Failed to add event listener.');

        if ($this->listenerData === null) {
            /** @var PrioritizedList<ListenerData> $listenerData */
            $listenerData       = new PrioritizedList();
            $this->listenerData = $listenerData;
        }

        $this->listenerData->add(new ListenerData($eventClass, $listener), $priority);

        if (\is_object($listener)) {
            $this->injectEnvironmentAndConfigurationIfNeeded($listener);
        } elseif (\is_array($listener) && \is_object($listener[0])) {
            $this->injectEnvironmentAndConfigurationIfNeeded($listener[0]);
        }

        return $this;
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

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
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
}
