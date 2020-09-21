<?php

declare(strict_types=1);

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UnicornFail\Emoji\Environment;

use UnicornFail\Emoji\Traits\ExtensibleEnvironmentTrait;
use UnicornFail\Emoji\Traits\ListeningEnvironmentTrait;
use UnicornFail\Emoji\Traits\RenderableEnvironmentTrait;

abstract class AbstractEnvironment implements EnvironmentInterface
{
    use ExtensibleEnvironmentTrait;
    use ListeningEnvironmentTrait;
    use RenderableEnvironmentTrait;

    /**
     * @var bool
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $initialized = false;

    /**
     * @throws \RuntimeException
     */
    protected function assertUninitialized(string $message): void
    {
        if ($this->initialized) {
            throw new \RuntimeException(\sprintf('%s The Environment has already been initialized.', $message));
        }
    }

    protected function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initializeExtensions();

        $this->initialized = true;
    }

    protected function injectEnvironmentAndConfigurationIfNeeded(object $object): void
    {
        if ($object instanceof EnvironmentAwareInterface) {
            $object->setEnvironment($this);
        }
    }
}
