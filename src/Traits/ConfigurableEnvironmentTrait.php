<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Traits;

use UnicornFail\Emoji\Configuration\ConfigurationInterface;

trait ConfigurableEnvironmentTrait
{
    /** @var ConfigurationInterface */
    private $configuration;

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->configuration = $configuration;
    }
}
