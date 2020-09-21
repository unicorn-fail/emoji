<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Traits;

use UnicornFail\Emoji\Extension\ExtensionInterface;

trait ExtensibleEnvironmentTrait
{
    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $extensions = [];

    /**
     * @var bool
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $extensionsInitialized = false;

    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $uninitializedExtensions = [];

    /**
     * @return iterable<ExtensionInterface>
     */
    protected static function defaultExtensions(): iterable
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function addExtension(ExtensionInterface $extension)
    {
        $this->assertUninitialized('Failed to add extension.');

        $this->extensions[]              = $extension;
        $this->uninitializedExtensions[] = $extension;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return ExtensionInterface[]
     */
    public function getExtensions(): iterable
    {
        return $this->extensions;
    }

    protected function initializeExtensions(): void
    {
        if ($this->extensionsInitialized) {
            return;
        }

        // Ask all extensions to register their components.
        while (\count($this->uninitializedExtensions) > 0) {
            foreach ($this->uninitializedExtensions as $i => $extension) {
                $extension->register($this);
                unset($this->uninitializedExtensions[$i]);
            }
        }

        $this->extensionsInitialized = true;
    }
}
