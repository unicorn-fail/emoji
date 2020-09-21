<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Environment;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

interface ListeningEnvironmentInterface extends EventDispatcherInterface, ListenerProviderInterface
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
    public function addEventListener(string $eventClass, callable $listener, int $priority = 0);
}
