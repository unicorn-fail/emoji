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

namespace League\Emoji\Environment;

use League\Configuration\Configuration;
use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use League\Emoji\Dataset\RuntimeDataset;
use League\Emoji\EmojiConverterInterface;
use League\Emoji\Emojibase\EmojibaseDatasetInterface;
use League\Emoji\Emojibase\EmojibaseShortcodeInterface;
use League\Emoji\Event\ListenerData;
use League\Emoji\Extension\ConfigurableExtensionInterface;
use League\Emoji\Extension\ConfigureConversionTypesInterface;
use League\Emoji\Extension\EmojiCoreExtension;
use League\Emoji\Extension\ExtensionInterface;
use League\Emoji\Renderer\NodeRendererInterface;
use League\Emoji\Util\PrioritizedList;

final class Environment implements EnvironmentBuilderInterface
{
    /** @var Configuration */
    private $config;

    /** @var ?RuntimeDataset */
    private $dataset;

    /** @var ?EventDispatcherInterface */
    private $eventDispatcher;

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
    private $initialized = false;

    /**
     * @var ?PrioritizedList<ListenerData>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $listenerData;

    /**
     * @var array<string, PrioritizedList<NodeRendererInterface>>
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $renderersByClass = [];

    /**
     * @var ExtensionInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    private $uninitializedExtensions = [];

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Configuration();
        $this->config->merge($config);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public static function create(array $configuration = []): self
    {
        $environment = new self($configuration);

        foreach (self::defaultExtensions() as $extension) {
            $environment->addExtension($extension);
        }

        return $environment;
    }

    /**
     * @param string|string[] $value
     *
     * @return string[]
     */
    public static function normalizeConvert($value): array
    {
        if (\is_array($value)) {
            return $value;
        }

        return \array_fill_keys(EmojiConverterInterface::TYPES, $value);
    }

    /**
     * @return ExtensionInterface[]
     */
    protected static function defaultExtensions(): iterable
    {
        return [new EmojiCoreExtension()];
    }

    public static function normalizeLocale(string $locale): string
    {
        /** @var string[] $normalized */
        static $normalized = [];

        // Immediately return if locale is an exact match.
        if (\in_array($locale, EmojibaseDatasetInterface::SUPPORTED_LOCALES, true)) {
            $normalized[$locale] = $locale;
        }

        // Immediately return if this local has already been normalized.
        if (isset($normalized[$locale])) {
            return $normalized[$locale];
        }

        $original              = $locale;
        $normalized[$original] = 'en';

        // Otherwise, see if it just needs some TLC.
        $locale = \strtolower($locale);
        $locale = \preg_replace('/[^a-z]/', '-', $locale) ?? $locale;
        foreach ([$locale, \current(\explode('-', $locale, 2))] as $locale) {
            if (\in_array($locale, EmojibaseDatasetInterface::SUPPORTED_LOCALES, true)) {
                $normalized[$original] = $locale;
                break;
            }
        }

        return $normalized[$original];
    }

    /**
     * @param string|string[] $presets
     *
     * @return string[]
     */
    public static function normalizePresets($presets): ?array
    {
        // Map preset aliases to their correct value.
        return \array_unique(\array_filter(\array_map(static function (string $preset): string {
            if (isset(EmojibaseShortcodeInterface::PRESET_ALIASES[$preset])) {
                return EmojibaseShortcodeInterface::PRESET_ALIASES[$preset];
            }

            return $preset;
        }, \array_values((array) $presets))));
    }

    public function addEventListener(string $eventClass, callable $listener, int $priority = 0): EnvironmentBuilderInterface
    {
        $this->assertUninitialized('Failed to add event listener.');

        if ($this->listenerData === null) {
            /** @var PrioritizedList<ListenerData> $listenerData */
            $listenerData       = new PrioritizedList();
            $this->listenerData = $listenerData;
        }

        $this->listenerData->add(new ListenerData($eventClass, $listener), $priority);

        $object = \is_array($listener)
            ? $listener[0]
            : $listener;

        if ($object instanceof EnvironmentAwareInterface) {
            $object->setEnvironment($this);
        }

        if ($object instanceof ConfigurationAwareInterface) {
            $object->setConfiguration($this->getConfiguration());
        }

        return $this;
    }

    public function addExtension(ExtensionInterface $extension): EnvironmentBuilderInterface
    {
        $this->assertUninitialized('Failed to add extension.');

        $this->extensions[]              = $extension;
        $this->uninitializedExtensions[] = $extension;

        if ($extension instanceof ConfigurableExtensionInterface) {
            $extension->configureSchema($this->config, $this->config->data());
        }

        return $this;
    }

    public function addRenderer(string $nodeClass, NodeRendererInterface $renderer, int $priority = 0): EnvironmentBuilderInterface
    {
        $this->assertUninitialized('Failed to add renderer.');

        if (! isset($this->renderersByClass[$nodeClass])) {
            /** @var PrioritizedList<NodeRendererInterface> $renderers */
            $renderers = new PrioritizedList();

            $this->renderersByClass[$nodeClass] = $renderers;
        }

        $this->renderersByClass[$nodeClass]->add($renderer, $priority);

        if ($renderer instanceof ConfigurationAwareInterface) {
            $renderer->setConfiguration($this->getConfiguration());
        }

        return $this;
    }

    /**
     * @throws \RuntimeException
     */
    protected function assertUninitialized(string $message): void
    {
        if ($this->initialized) {
            throw new \RuntimeException(\sprintf('%s The Environment has already been initialized.', $message));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event)
    {
        $this->initialize();

        if ($this->eventDispatcher !== null) {
            return $this->eventDispatcher->dispatch($event);
        }

        foreach ($this->getListenersForEvent($event) as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }

            $listener($event);
        }

        return $event;
    }

    public function getConfiguration(): ConfigurationInterface
    {
        $this->initializeConfiguration();

        return $this->config->reader();
    }

    public function getRuntimeDataset(string $index = 'hexcode'): RuntimeDataset
    {
        $this->initialize();

        if ($this->dataset === null) {
            $this->dataset = new RuntimeDataset($this->getConfiguration());
        }

        return $this->dataset->indexBy($index);
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

    /**
     * {@inheritDoc}
     *
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($this->listenerData === null) {
            /** @var PrioritizedList<ListenerData> $listenerData */
            $listenerData       = new PrioritizedList();
            $this->listenerData = $listenerData;
        }

        /** @var ListenerData $listenerData */
        foreach ($this->listenerData as $listenerData) {
            if (! \is_a($event, $listenerData->getEvent())) {
                continue;
            }

            yield function (object $event) use ($listenerData): void {
                $this->initialize();

                \call_user_func($listenerData->getListener(), $event);
            };
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderersForClass(string $nodeClass): iterable
    {
        $this->initialize();

        // If renderers are defined for this specific class, return them immediately
        if (isset($this->renderersByClass[$nodeClass])) {
            return $this->renderersByClass[$nodeClass];
        }

        while (\class_exists($parent = (string) ($parent ?? $nodeClass)) && ($parent = \get_parent_class($parent))) {
            if (! isset($this->renderersByClass[$parent])) {
                continue;
            }

            // "Cache" this result to avoid future loops
            return $this->renderersByClass[$nodeClass] = $this->renderersByClass[$parent];
        }

        return [];
    }

    protected function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initializeConfiguration();

        $this->initializeExtensions();

        $this->initialized = true;
    }

    protected function initializeConfiguration(): void
    {
        $this->config->addSchema('allow_unsafe_links', Expect::bool(true));

        $default = EmojiConverterInterface::UNICODE;

        /** @var string[] $conversionTypes */
        $conversionTypes = (array) EmojiConverterInterface::TYPES;

        foreach ($this->extensions as $extension) {
            if ($extension instanceof ConfigureConversionTypesInterface) {
                $extension->configureConversionTypes($default, $conversionTypes, $this->config->data());
            }
        }

        $conversionTypes = \array_unique($conversionTypes);

        $structuredConversionTypes = Expect::structure(\array_combine(
            EmojiConverterInterface::TYPES,
            \array_map(static function (string $conversionType) use ($conversionTypes, $default): Schema {
                return Expect::anyOf(false, ...$conversionTypes)->default($default)->nullable();
            }, EmojiConverterInterface::TYPES)
        ))->castTo('array');

        $this->config->addSchema('convert', Expect::anyOf($structuredConversionTypes, ...$conversionTypes)
            ->default(\array_fill_keys(EmojiConverterInterface::TYPES, $default))
            ->before('\League\Emoji\Environment\Environment::normalizeConvert'));

        $this->config->addSchema('exclude', Expect::structure([
            'shortcodes' => Expect::arrayOf('string')
                ->default([])
                ->before('\League\Emoji\Util\Normalize::shortcodes'),
        ])->castTo('array'));

        $this->config->addSchema('locale', Expect::anyOf(...EmojibaseDatasetInterface::SUPPORTED_LOCALES)
            ->default('en')
            ->before('\League\Emoji\Environment\Environment::normalizeLocale'));

        $this->config->addSchema('native', Expect::bool()->nullable());

        $this->config->addSchema('presentation', Expect::anyOf(...EmojibaseDatasetInterface::SUPPORTED_PRESENTATIONS)
            ->default(EmojibaseDatasetInterface::EMOJI));

        $this->config->addSchema('preset', Expect::anyOf(Expect::listOf(Expect::anyOf(...EmojibaseShortcodeInterface::SUPPORTED_PRESETS)), ...EmojibaseShortcodeInterface::SUPPORTED_PRESETS)
            ->default(EmojibaseShortcodeInterface::DEFAULT_PRESETS)
            ->before('\League\Emoji\Environment\Environment::normalizePresets'));
    }

    protected function initializeExtensions(): void
    {
        // Ask all extensions to register their components.
        while (\count($this->uninitializedExtensions) > 0) {
            foreach ($this->uninitializedExtensions as $i => $extension) {
                $extension->register($this);
                unset($this->uninitializedExtensions[$i]);
            }
        }
    }

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }
}
