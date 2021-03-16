<?php

declare(strict_types=1);

namespace League\Emoji\Extension;

use Dflydev\DotAccessData\Data;

interface ConfigureConversionTypesInterface
{
    /** @param string[] $conversionTypes */
    public function configureConversionTypes(string &$default, array &$conversionTypes, Data $rawConfig): void;
}
