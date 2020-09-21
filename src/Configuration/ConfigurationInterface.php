<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Configuration;

use Dflydev\DotAccessData\DataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ConfigurationInterface extends \IteratorAggregate, DataInterface
{
    public function configureOptions(OptionsResolver $resolver): void;
}
