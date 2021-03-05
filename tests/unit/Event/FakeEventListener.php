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

namespace UnicornFail\Emoji\Tests\Unit\Event;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use UnicornFail\Emoji\Environment\EnvironmentAwareInterface;
use UnicornFail\Emoji\Environment\EnvironmentInterface;
use UnicornFail\Emoji\Event\AbstractEvent;

class FakeEventListener implements ConfigurationAwareInterface, EnvironmentAwareInterface
{
    /** @var callable */
    private $callback;

    /** @var ConfigurationInterface */
    private $configuration;

    /** @var EnvironmentInterface */
    private $environment;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function setEnvironment(EnvironmentInterface $environment): void
    {
        $this->environment = $environment;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    public function getEnvironment(): EnvironmentInterface
    {
        return $this->environment;
    }

    /**
     * @return mixed
     */
    public function doStuff(AbstractEvent $event)
    {
        return \call_user_func($this->callback, $event);
    }
}
