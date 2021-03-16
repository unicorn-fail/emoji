<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Extension\Twemoji;

use Dflydev\DotAccessData\Data;
use League\Configuration\ConfigurationBuilderInterface;
use Nette\Schema\Expect;
use UnicornFail\Emoji\Environment\EnvironmentBuilderInterface;
use UnicornFail\Emoji\Event\DocumentParsedEvent;
use UnicornFail\Emoji\Extension\ConfigurableExtensionInterface;
use UnicornFail\Emoji\Extension\ConfigureConversionTypesInterface;

final class TwemojiExtension implements ConfigurableExtensionInterface, ConfigureConversionTypesInterface
{
    public const CONVERSION_TYPE = TwemojiProcessor::CONVERSION_TYPE;

    /** @var bool */
    private $setAsDefaultConversionType;

    public function __construct(bool $setAsDefaultConversionType = true)
    {
        $this->setAsDefaultConversionType = $setAsDefaultConversionType;
    }

    /**
     * {@inheritDoc}
     */
    public function configureConversionTypes(string &$default, array &$conversionTypes, Data $rawConfig): void
    {
        // Set the default conversion type to Twemoji.
        if ($this->setAsDefaultConversionType) {
            $default = TwemojiProcessor::CONVERSION_TYPE;
        }

        $conversionTypes[] = TwemojiProcessor::CONVERSION_TYPE;
    }

    public function configureSchema(ConfigurationBuilderInterface $builder, Data $rawConfig): void
    {
        $builder->addSchema('twemoji', Expect::structure([
            'classes'     => Expect::arrayOf('string')->default(['twemoji']),
            'classPrefix' => Expect::string('twemoji-')->nullable(),
            'icon'        => Expect::bool(false)->nullable(),
            'inline'      => Expect::bool(true),
            'size'        => Expect::type('int|float|string')->nullable(),
            'type'        => Expect::anyOf('png', 'svg')->default('svg'),
            'urlBase'     => Expect::string('https://twemoji.maxcdn.com/v/latest'),
        ]));
    }

    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentParsedEvent::class, new TwemojiProcessor());
    }
}
