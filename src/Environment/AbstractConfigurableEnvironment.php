<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Environment;

use UnicornFail\Emoji\Configuration\Configuration;
use UnicornFail\Emoji\Configuration\ConfigurationAwareInterface;
use UnicornFail\Emoji\Traits\ConfigurableEnvironmentTrait;

class AbstractConfigurableEnvironment extends AbstractEnvironment implements ConfigurableEnvironmentInterface
{
    use ConfigurableEnvironmentTrait;

    /**
     * @param mixed[]|\Traversable $configuration
     */
    public function __construct(?iterable $configuration = null)
    {
        $this->configuration = Configuration::create($configuration);
    }

    protected function injectEnvironmentAndConfigurationIfNeeded(object $object): void
    {
        parent::injectEnvironmentAndConfigurationIfNeeded($object);

        if ($object instanceof ConfigurationAwareInterface) {
            $object->setConfiguration($this->getConfiguration());
        }
    }
}
