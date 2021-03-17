<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji\Tests\Unit\Environment;

use League\Configuration\ConfigurationInterface;
use League\Configuration\MutableConfigurationInterface;
use League\Emoji\Environment\Environment;
use League\Emoji\Event\AbstractEvent;
use League\Emoji\Extension\ExtensionInterface;
use League\Emoji\Renderer\NodeRendererInterface;
use League\Emoji\Tests\Unit\Event\FakeEvent;
use League\Emoji\Tests\Unit\Event\FakeEventListener;
use League\Emoji\Tests\Unit\Event\FakeEventListenerInvokable;
use League\Emoji\Tests\Unit\Event\FakeEventParent;
use League\Emoji\Tests\Unit\Node\Node1;
use League\Emoji\Tests\Unit\Node\Node3;
use League\Emoji\Util\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class EnvironmentTest extends TestCase
{
    public function testAddGetExtensions(): void
    {
        $environment = new Environment();
        $this->assertCount(0, $environment->getExtensions());

        $firstExtension = $this->createMock(ExtensionInterface::class);
        $firstExtension->expects($this->once())
            ->method('register')
            ->with($environment);

        $environment->addExtension($firstExtension);

        $extensions = $environment->getExtensions();
        $this->assertCount(1, $extensions);
        $this->assertEquals($firstExtension, $extensions[0]);

        $secondExtension = $this->createMock(ExtensionInterface::class);
        $secondExtension->expects($this->once())
            ->method('register')
            ->with($environment);
        $environment->addExtension($secondExtension);

        $extensions = $environment->getExtensions();

        $this->assertCount(2, $extensions);
        $this->assertEquals($firstExtension, $extensions[0]);
        $this->assertEquals($secondExtension, $extensions[1]);

        // Trigger initialization
        $environment->getRuntimeDataset();
    }

    public function testConstructor(): void
    {
        $environment = new Environment(['allow_unsafe_links' => false]);
        $this->assertFalse($environment->getConfiguration()->get('allow_unsafe_links'));
    }

    public function testGetConfiguration(): void
    {
        $environment = new Environment(['allow_unsafe_links' => false]);

        $configuration = $environment->getConfiguration();
        $this->assertInstanceOf(ConfigurationInterface::class, $configuration);
        $this->assertNotInstanceOf(MutableConfigurationInterface::class, $configuration);
        $this->assertFalse($configuration->get('allow_unsafe_links'));
    }

    public function testAddRenderer(): void
    {
        $environment = new Environment();

        $renderer = $this->createMock(NodeRendererInterface::class);
        $environment->addRenderer('MyClass', $renderer);

        $this->assertContains($renderer, $environment->getRenderersForClass('MyClass'));
    }

    public function testAddRendererFailsAfterInitialization(): void
    {
        $this->expectException(\RuntimeException::class);

        $environment = new Environment();

        // This triggers the initialization
        $environment->getRenderersForClass('MyClass');

        $renderer = $this->createMock(NodeRendererInterface::class);
        $environment->addRenderer('MyClass', $renderer);
    }

    public function testGetRendererForUnknownClass(): void
    {
        $environment  = new Environment();
        $mockRenderer = $this->createMock(NodeRendererInterface::class);
        $environment->addRenderer(Node3::class, $mockRenderer);

        $this->assertEmpty($environment->getRenderersForClass(Node1::class));
    }

    public function testGetRendererForSubClass(): void
    {
        $environment  = new Environment();
        $mockRenderer = $this->createMock(NodeRendererInterface::class);
        $environment->addRenderer(Node1::class, $mockRenderer);

        // Ensure the parent renderer is returned
        $this->assertFirstResult($mockRenderer, $environment->getRenderersForClass(Node3::class));
        // Check again to ensure any cached result is also the same
        $this->assertFirstResult($mockRenderer, $environment->getRenderersForClass(Node3::class));
    }

    public function testAddExtensionAndGetter(): void
    {
        $environment = new Environment();

        $extension = $this->createMock(ExtensionInterface::class);
        $environment->addExtension($extension);

        $this->assertContains($extension, $environment->getExtensions());
    }

    public function testAddExtensionFailsAfterInitialization(): void
    {
        $this->expectException(\RuntimeException::class);

        $environment = new Environment();

        // This triggers the initialization
        $environment->getRenderersForClass('MyClass');

        $extension = $this->createMock(ExtensionInterface::class);
        $environment->addExtension($extension);
    }

    public function testInjectableEventListenersGetInjected(): void
    {
        $environment = new Environment();

        // phpcs:ignore Squiz.WhiteSpace.ScopeClosingBrace.ContentBefore
        $listener1 = new FakeEventListener(static function (): void { });
        // phpcs:ignore Squiz.WhiteSpace.ScopeClosingBrace.ContentBefore
        $listener2 = new FakeEventListenerInvokable(static function (): void { });

        $environment->addEventListener('', [$listener1, 'doStuff']);
        $environment->addEventListener('', $listener2);

        // Trigger initialization
        $environment->getRuntimeDataset();

        $this->assertSame($environment, $listener1->getEnvironment());
        $this->assertSame($environment, $listener2->getEnvironment());

        $this->assertNotNull($listener1->getConfiguration());
        $this->assertNotNull($listener2->getConfiguration());
    }

    public function testRendererPrioritization(): void
    {
        $environment = new Environment();

        $renderer1 = $this->createMock(NodeRendererInterface::class);
        $renderer2 = $this->createMock(NodeRendererInterface::class);
        $renderer3 = $this->createMock(NodeRendererInterface::class);

        $environment->addRenderer('foo', $renderer1);
        $environment->addRenderer('foo', $renderer2, 50);
        $environment->addRenderer('foo', $renderer3);

        $parsers = \iterator_to_array($environment->getRenderersForClass('foo'));

        $this->assertSame($renderer2, $parsers[0]);
        $this->assertSame($renderer1, $parsers[1]);
        $this->assertSame($renderer3, $parsers[2]);
    }

    public function testEventDispatching(): void
    {
        $environment = new Environment();
        $event       = new FakeEvent();

        $actualOrder = [];

        $environment->addEventListener(FakeEvent::class, function (FakeEvent $e) use ($event, &$actualOrder): void {
            $this->assertSame($event, $e);
            $actualOrder[] = 'a';
        });

        // Listeners on parent classes should also be called
        $environment->addEventListener(FakeEventParent::class, function (FakeEvent $e) use ($event, &$actualOrder): void {
            $this->assertSame($event, $e);
            $actualOrder[] = 'b';
            $e->stopPropagation();
        });

        $environment->addEventListener(FakeEvent::class, function (FakeEvent $e) use ($event, &$actualOrder): void {
            $this->assertSame($event, $e);
            $actualOrder[] = 'c';
        }, 10);

        $environment->addEventListener(FakeEvent::class, function (FakeEvent $e): void {
            $this->fail('Propogation should have been stopped before here');
        });

        $environment->dispatch($event);

        $this->assertCount(3, $actualOrder);
        $this->assertEquals('c', $actualOrder[0]);
        $this->assertEquals('a', $actualOrder[1]);
        $this->assertEquals('b', $actualOrder[2]);
    }

    public function testAddEventListenerFailsAfterInitialization(): void
    {
        $this->expectException(\RuntimeException::class);

        $environment = new Environment();

        // Trigger initialization
        $environment->dispatch($this->createMock(AbstractEvent::class));

        $environment->addEventListener(AbstractEvent::class, static function (AbstractEvent $e): void {
        });
    }

    public function testDispatchDelegatesToProvidedDispatcher(): void
    {
        $dispatchersCalled = new ArrayCollection();

        $environment = new Environment();

        $environment->addEventListener(FakeEvent::class, static function (FakeEvent $event) use ($dispatchersCalled): void {
            $dispatchersCalled[] = 'THIS SHOULD NOT BE CALLED!';
        });

        $environment->setEventDispatcher(new class ($dispatchersCalled) implements EventDispatcherInterface {
            /** @var ArrayCollection */
            private $dispatchersCalled;

            public function __construct(ArrayCollection $dispatchersCalled)
            {
                $this->dispatchersCalled = $dispatchersCalled;
            }

            public function dispatch(object $event): void
            {
                $this->dispatchersCalled[] = 'external';
            }
        });

        $environment->dispatch(new FakeEvent());

        $this->assertCount(1, $dispatchersCalled);
        $this->assertSame('external', $dispatchersCalled->first());
    }

    /**
     * @param mixed           $expected
     * @param iterable<mixed> $actual
     */
    private function assertFirstResult($expected, iterable $actual): void
    {
        foreach ($actual as $a) {
            $this->assertSame($expected, $a);

            return;
        }

        $this->assertSame($expected, null);
    }
}
