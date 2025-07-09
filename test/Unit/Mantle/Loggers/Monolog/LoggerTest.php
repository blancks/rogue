<?php

declare(strict_types=1);

namespace Test\Unit\Mantle\Loggers\Monolog;

use LogicException;
use Monolog\Handler\HandlerInterface as MonologHandlerInterface;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Psr\Log\LogLevel;
use Mantle\Contracts\LoggerHandlerInterface;
use Mantle\Loggers\Monolog\Logger;
use Mantle\Loggers\Monolog\Processors\ProcessorInterface;
use ValueError;

class LoggerTest extends TestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger('test');
    }

    public function testConstructorWithDefaults(): void
    {
        // Act
        $logger = new Logger();

        // Assert
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertTrue($logger->hasChannel('app'));
        $this->assertInstanceOf(PsrLoggerInterface::class, $logger->default());
    }

    public function testConstructorWithCustomChannel(): void
    {
        // Act
        $logger = new Logger('custom');

        // Assert
        $this->assertTrue($logger->hasChannel('custom'));
        $this->assertInstanceOf(PsrLoggerInterface::class, $logger->channel('custom'));
    }

    public function testConstructorWithCustomHandlers(): void
    {
        // Arrange
        $handler = $this->createMock(LoggerHandlerInterface::class);
        $monologHandler = $this->createMock(MonologHandlerInterface::class);
        $handler->method('getHandler')->willReturn($monologHandler);
        $handlers = [$handler];

        // Act
        $logger = new Logger('test', $handlers);

        // Assert
        $this->assertTrue($logger->hasChannel('test'));
        $this->assertInstanceOf(PsrLoggerInterface::class, $logger->channel('test'));
    }

    public function testConstructorWithCustomProcessors(): void
    {
        // Arrange
        $processor = function (array $record): array {
            $record['extra']['test'] = 'value';
            return $record;
        };
        $processors = [$processor];

        // Act
        $logger = new Logger('test', null, $processors);

        // Assert
        $this->assertTrue($logger->hasChannel('test'));
        $this->assertInstanceOf(PsrLoggerInterface::class, $logger->channel('test'));
    }

    public function testConstructorWithInvalidChannelName(): void
    {
        // Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Invalid channel name: invalid@channel');

        // Act
        new Logger('invalid@channel');
    }

    public function testChannelCreatesNewChannelIfNotExists(): void
    {
        // Act
        $channelLogger = $this->logger->channel('new-channel');

        // Assert
        $this->assertInstanceOf(PsrLoggerInterface::class, $channelLogger);
        $this->assertTrue($this->logger->hasChannel('new-channel'));
    }

    public function testChannelReturnsExistingChannel(): void
    {
        // Arrange
        $this->logger->register('existing');

        // Act
        $channelLogger1 = $this->logger->channel('existing');
        $channelLogger2 = $this->logger->channel('existing');

        // Assert
        $this->assertSame($channelLogger1, $channelLogger2);
    }

    public function testChannelWithInvalidName(): void
    {
        // Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Invalid channel name: invalid@channel');

        // Act
        $this->logger->channel('invalid@channel');
    }

    public function testChannelNameIsCaseInsensitive(): void
    {
        // Act
        $logger1 = $this->logger->channel('Test');
        $logger2 = $this->logger->channel('test');

        // Assert
        $this->assertSame($logger1, $logger2);
        $this->assertTrue($this->logger->hasChannel('test'));
    }

    public function testDefaultReturnsDefaultChannel(): void
    {
        // Act
        $defaultLogger = $this->logger->default();

        // Assert
        $this->assertInstanceOf(PsrLoggerInterface::class, $defaultLogger);
        $this->assertSame($defaultLogger, $this->logger->channel('test'));
    }

    public function testRegisterCreatesNewChannel(): void
    {
        // Act
        $this->logger->register('new-channel');

        // Assert
        $this->assertTrue($this->logger->hasChannel('new-channel'));
        $this->assertInstanceOf(PsrLoggerInterface::class, $this->logger->channel('new-channel'));
    }

    public function testRegisterWithHandlers(): void
    {
        // Arrange
        $handler = $this->createMock(LoggerHandlerInterface::class);
        $monologHandler = $this->createMock(MonologHandlerInterface::class);
        $handler->method('getHandler')->willReturn($monologHandler);
        $handlers = [$handler];

        // Act
        $this->logger->register('test-channel', $handlers);

        // Assert
        $this->assertTrue($this->logger->hasChannel('test-channel'));
    }

    public function testRegisterWithProcessors(): void
    {
        // Arrange
        $processor = function (array $record): array {
            $record['extra']['test'] = 'value';
            return $record;
        };
        $processors = [$processor];

        // Act
        $this->logger->register('test-channel', [], $processors);

        // Assert
        $this->assertTrue($this->logger->hasChannel('test-channel'));
    }

    public function testRegisterWithInvalidChannelName(): void
    {
        // Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Invalid channel name: invalid@channel');

        // Act
        $this->logger->register('invalid@channel');
    }

    public function testAddHandlerToExistingChannel(): void
    {
        // Arrange
        $this->logger->register('test-channel');
        $handler = $this->createMock(LoggerHandlerInterface::class);
        $monologHandler = $this->createMock(MonologHandlerInterface::class);
        $handler->expects($this->once())
            ->method('getHandler')
            ->willReturn($monologHandler);

        // Act
        $this->logger->addHandler('test-channel', $handler);

        // Assert - No exception should be thrown
        $this->assertTrue($this->logger->hasChannel('test-channel'));
    }

    public function testAddHandlerToNonExistentChannel(): void
    {
        // Arrange
        $handler = $this->createMock(LoggerHandlerInterface::class);

        // Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown channel: non-existent');

        // Act
        $this->logger->addHandler('non-existent', $handler);
    }

    public function testAddProcessorWithCallable(): void
    {
        // Arrange
        $this->logger->register('test-channel');
        $processor = function (array $record): array {
            $record['extra']['test'] = 'value';
            return $record;
        };

        // Act
        $this->logger->addProcessor('test-channel', $processor);

        // Assert - No exception should be thrown
        $this->assertTrue($this->logger->hasChannel('test-channel'));
    }

    public function testAddProcessorWithProcessorInterface(): void
    {
        // Arrange
        $this->logger->register('test-channel');
        $processor = $this->createMock(ProcessorInterface::class);
        $callableProcessor = function (array $record): array {
            return $record;
        };
        $processor->expects($this->once())
            ->method('getProcessor')
            ->willReturn($callableProcessor);

        // Act
        $this->logger->addProcessor('test-channel', $processor);

        // Assert - No exception should be thrown
        $this->assertTrue($this->logger->hasChannel('test-channel'));
    }

    public function testAddProcessorWithMonologProcessor(): void
    {
        // Arrange
        $this->logger->register('test-channel');
        $processor = $this->createMock(ProcessorInterface::class);
        $monologProcessor = $this->createMock(MonologProcessorInterface::class);
        $processor->expects($this->once())
            ->method('getProcessor')
            ->willReturn($monologProcessor);

        // Act
        $this->logger->addProcessor('test-channel', $processor);

        // Assert - No exception should be thrown
        $this->assertTrue($this->logger->hasChannel('test-channel'));
    }

    public function testAddProcessorWithInvalidProcessor(): void
    {
        // Arrange
        $this->logger->register('test-channel');

        // Create a LoggerProcessorInterface that is NOT a ProcessorInterface
        $invalidProcessor = new class () implements \Mantle\Contracts\LoggerProcessorInterface {
            public function getProcessor(): object|callable
            {
                return function (array $record): array {
                    return $record;
                };
            }
        };

        // Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Invalid processor. Must provide an instance of ProcessorInterface');

        // Act
        $this->logger->addProcessor('test-channel', $invalidProcessor);
    }

    public function testAddProcessorWithCompletelyInvalidType(): void
    {
        // Arrange
        $this->logger->register('test-channel');
        $invalidProcessor = 'not-a-processor';

        // Assert
        $this->expectException(\TypeError::class);

        // Act
        $this->logger->addProcessor('test-channel', $invalidProcessor);
    }

    public function testAddProcessorToNonExistentChannel(): void
    {
        // Arrange
        $processor = function (array $record): array {
            return $record;
        };

        // Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown channel: non-existent');

        // Act
        $this->logger->addProcessor('non-existent', $processor);
    }

    public function testHasChannelReturnsTrueForExistingChannel(): void
    {
        // Arrange
        $this->logger->register('existing');

        // Act & Assert
        $this->assertTrue($this->logger->hasChannel('existing'));
    }

    public function testHasChannelReturnsFalseForNonExistentChannel(): void
    {
        // Act & Assert
        $this->assertFalse($this->logger->hasChannel('non-existent'));
    }

    public function testSetMinLevelWithDefaultChannel(): void
    {
        // Act
        $this->logger->setMinLevel(LogLevel::ERROR);

        // Assert - No exception should be thrown
        $this->assertTrue($this->logger->hasChannel('test'));
    }

    public function testSetMinLevelWithSpecificChannel(): void
    {
        // Arrange
        $this->logger->register('specific');

        // Act
        $this->logger->setMinLevel(LogLevel::WARNING, 'specific');

        // Assert - No exception should be thrown
        $this->assertTrue($this->logger->hasChannel('specific'));
    }

    public function testSetMinLevelWithNonExistentChannel(): void
    {
        // Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown channel: non-existent');

        // Act
        $this->logger->setMinLevel(LogLevel::ERROR, 'non-existent');
    }

    public function testSetMinLevelWithInvalidLevel(): void
    {
        // Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Invalid level name: invalid-level');

        // Act
        $this->logger->setMinLevel('invalid-level');
    }

    public function testSetMinLevelWithInvalidChannelName(): void
    {
        // Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Invalid channel name: invalid@channel');

        // Act
        $this->logger->setMinLevel(LogLevel::ERROR, 'invalid@channel');
    }

    public function testValidChannelNames(): void
    {
        $validNames = [
            'app',
            'test-channel',
            'test_channel',
            'test/channel',
            'test123',
            'TEST', // Should be converted to lowercase
            'a-b_c/d123'
        ];

        foreach ($validNames as $name) {
            // Act
            $this->logger->register($name);

            // Assert
            $this->assertTrue($this->logger->hasChannel(strtolower($name)));
        }
    }

    public function testInvalidChannelNames(): void
    {
        $invalidNames = [
            'test@channel',
            'test.channel',
            'test channel',
            'test+channel',
            'test#channel',
            'test$channel',
            'test%channel',
            'test^channel',
            'test&channel',
            'test*channel',
            'test(channel',
            'test)channel',
            'test=channel',
            'test[channel',
            'test]channel',
            'test{channel',
            'test}channel',
            'test|channel',
            'test\\channel',
            'test;channel',
            'test:channel',
            'test"channel',
            'test\'channel',
            'test<channel',
            'test>channel',
            'test,channel',
            'test?channel',
            'test!channel',
            'test~channel',
            'test`channel',
            ''
        ];

        foreach ($invalidNames as $name) {
            try {
                $this->logger->register($name);
                $this->fail("Expected ValueError for invalid channel name: $name");
            } catch (ValueError $e) {
                $this->assertStringContainsString('Invalid channel name:', $e->getMessage());
            }
        }
    }

    public function testProcessorIsAddedSuccessfully(): void
    {
        // Arrange
        $this->logger->register('processor-test');
        $processor = function (array $record): array {
            $record['extra']['custom'] = 'test-value';
            return $record;
        };

        // Act - This should not throw an exception
        $this->logger->addProcessor('processor-test', $processor);

        // Assert
        $this->assertTrue($this->logger->hasChannel('processor-test'));
    }
}
