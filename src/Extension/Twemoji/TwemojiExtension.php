<?php

declare(strict_types=1);

namespace League\Emoji\Extension\Twemoji;

use Dflydev\DotAccessData\Data;
use League\Configuration\ConfigurationBuilderInterface;
use League\Emoji\Environment\EnvironmentBuilderInterface;
use League\Emoji\Event\DocumentParsedEvent;
use League\Emoji\Extension\ConfigurableExtensionInterface;
use League\Emoji\Extension\ConfigureConversionTypesInterface;
use Nette\Schema\Expect;

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
