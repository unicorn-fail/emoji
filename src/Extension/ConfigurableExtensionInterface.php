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

namespace UnicornFail\Emoji\Extension;

use Dflydev\DotAccessData\Data;
use League\Configuration\ConfigurationBuilderInterface;

interface ConfigurableExtensionInterface extends ExtensionInterface
{
    public function configureSchema(ConfigurationBuilderInterface $builder, Data $rawConfig): void;
}
