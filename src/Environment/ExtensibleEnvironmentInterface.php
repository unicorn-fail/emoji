<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Environment;

use UnicornFail\Emoji\Extension\ExtensionInterface;

interface ExtensibleEnvironmentInterface
{
    /**
     * Registers the given extension with the Environment
     *
     * @return static
     */
    public function addExtension(ExtensionInterface $extension);

    /**
     * Get all registered extensions
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions(): iterable;
}
