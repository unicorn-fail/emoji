<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Environment;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationBuilderInterface;
use League\Configuration\ConfigurationInterface;

abstract class AbstractConfigurableEnvironment extends AbstractEnvironment implements ConfigurableEnvironmentInterface
{
    /** @var ConfigurationBuilderInterface */
    private $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = static::createDefaultConfiguration();
        $this->config->merge($config);
    }

    /**
     * @return ConfigurationBuilderInterface
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->config;
    }

    protected function injectEnvironmentAndConfigurationIfNeeded(object $object): void
    {
        parent::injectEnvironmentAndConfigurationIfNeeded($object);

        if ($object instanceof ConfigurationAwareInterface) {
            $object->setConfiguration($this->getConfiguration());
        }
    }

    abstract public static function createDefaultConfiguration(): ConfigurationBuilderInterface;
}
