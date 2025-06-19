<?php

declare(strict_types=1);

namespace Rogue\Test\Unit\Mantle\Events;

use PHPUnit\Framework\TestCase;
use Rogue\Mantle\Events\EventDispatcher;

class EventDispatcherTest extends TestCase
{
    public function testListenerIsCalledOnDispatch(): void
    {
        // Arrange
        $dispatcher = new EventDispatcher();
        $closureCalled = false;
        $closureInputParameterValue = null;
        $closureInputEventName = null;

        // Act
        $payload = ['bar'];

        $dispatcher->listen(
            'test.event',
            function ($foo, $eventName) use (
                &$closureCalled,
                &$closureInputParameterValue,
                &$closureInputEventName
            ) {
                $closureInputParameterValue = $foo;
                $closureInputEventName = $eventName;
                $closureCalled = true;
            }
        );

        $dispatcher->dispatch('test.event', $payload);

        // Assert
        $this->assertTrue(
            condition: $closureCalled,
            message: 'Listener was not called.'
        );

        $this->assertSame(
            expected: 'bar',
            actual: $closureInputParameterValue,
            message: 'Listener input parameter does not match payload parameter.'
        );

        $this->assertSame(
            expected: 'test.event',
            actual: $closureInputEventName,
            message: 'Listener event name does not match dispatched event name.'
        );
    }

    public function testMultipleListenersAreCalled(): void
    {
        // Arrange
        $calls = 0;
        $closure = function () use (&$calls) { ++$calls; };
        $dispatcher = new EventDispatcher();

        // Act
        $dispatcher->listen('multi.event', $closure);
        $dispatcher->listen('multi.event', $closure);
        $dispatcher->dispatch('multi.event');

        // Assert
        $this->assertSame(
            expected: 2,
            actual: $calls,
            message: 'Not all listeners were called.'
        );
    }

    public function testNoListenersDoesNotThrow(): void
    {
        $dispatcher = new EventDispatcher();

        // Should not throw
        $dispatcher->dispatch('no.listeners');

        $this->assertTrue(true);
    }

    public function testPayloadIsPassedToListener(): void
    {
        // Arrange
        $dispatcher = new EventDispatcher();
        $closureInputParameter1 = null;
        $closureInputParameter2 = null;
        $closureInputEventName = null;

        // Act
        $dispatcher->listen(
            'payload.event',
            function ($a, $b, $eventName) use (
                &$closureInputParameter1,
                &$closureInputParameter2,
                &$closureInputEventName
            ) {
                $closureInputParameter1 = $a;
                $closureInputParameter2 = $b;
                $closureInputEventName = $eventName;
            }
        );

        $dispatcher->dispatch('payload.event', [1, 2]);

        // Assert
        $this->assertSame(
            expected: 1,
            actual: $closureInputParameter1,
            message: 'Listener input parameter 1 does not match payload parameter 1.'
        );

        $this->assertSame(
            expected: 2,
            actual: $closureInputParameter2,
            message: 'Listener input parameter 2 does not match payload parameter 2.'
        );

        $this->assertSame(
            expected: 'payload.event',
            actual: $closureInputEventName,
            message: 'Listener event name does not match dispatched event name.'
        );
    }
}
