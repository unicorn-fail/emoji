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

namespace UnicornFail\Emoji\Event;

/**
 * @internal
 *
 * @psalm-immutable
 */
final class ListenerData
{
    /** @var string */
    private $event;

    /** @var callable */
    private $listener;

    public function __construct(string $event, callable $listener)
    {
        $this->event    = $event;
        $this->listener = $listener;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getListener(): callable
    {
        return $this->listener;
    }
}
