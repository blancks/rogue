<?php

declare(strict_types=1);

namespace Test\Integration\Mantle\Loggers\Monolog;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Mantle\Loggers\Monolog\Handlers\StreamHandler;
use Mantle\Loggers\Monolog\Logger;

class LoggerIntegrationTest extends TestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger('integration-test');
    }

    public function testLoggerIntegrationWithMonolog(): void
    {
        // Arrange
        $stream = fopen('php://memory', 'w+');
        $handler = new StreamHandler($stream);
        $this->logger->register('integration', [$handler]);

        // Act
        $logger = $this->logger->channel('integration');
        $logger->info('Test message', ['key' => 'value']);

        // Assert
        rewind($stream);
        $output = stream_get_contents($stream);
        $this->assertStringContainsString('Test message', $output);
        $this->assertStringContainsString('integration', $output);
        fclose($stream);
    }

    public function testMultipleChannelsWorkIndependently(): void
    {
        // Arrange
        $stream1 = fopen('php://memory', 'w+');
        $stream2 = fopen('php://memory', 'w+');
        $handler1 = new StreamHandler($stream1);
        $handler2 = new StreamHandler($stream2);

        $this->logger->register('channel1', [$handler1]);
        $this->logger->register('channel2', [$handler2]);

        // Act
        $logger1 = $this->logger->channel('channel1');
        $logger2 = $this->logger->channel('channel2');

        $logger1->info('Message to channel 1');
        $logger2->info('Message to channel 2');

        // Assert
        rewind($stream1);
        $output1 = stream_get_contents($stream1);
        $this->assertStringContainsString('Message to channel 1', $output1);
        $this->assertStringNotContainsString('Message to channel 2', $output1);

        rewind($stream2);
        $output2 = stream_get_contents($stream2);
        $this->assertStringContainsString('Message to channel 2', $output2);
        $this->assertStringNotContainsString('Message to channel 1', $output2);

        fclose($stream1);
        fclose($stream2);
    }

    public function testProcessorModifiesLogRecord(): void
    {
        // Arrange
        $stream = fopen('php://memory', 'w+');
        $handler = new StreamHandler($stream);
        $processorCalled = false;
        $processor = function (array $record) use (&$processorCalled): array {
            $processorCalled = true;
            $record['extra']['custom'] = 'test-value';
            return $record;
        };

        $this->logger->register('processor-test', [$handler], [$processor]);

        // Act
        $logger = $this->logger->channel('processor-test');
        $logger->info('Test message');

        // Assert
        $this->assertTrue($processorCalled, 'Processor was not called');
        rewind($stream);
        $output = stream_get_contents($stream);
        $this->assertStringContainsString('Test message', $output);
        fclose($stream);
    }

    public function testLogLevelFilteringWorksEndToEnd(): void
    {
        // Arrange
        $stream = fopen('php://memory', 'w+');
        $handler = new StreamHandler($stream);
        $this->logger->register('level-test', [$handler]);

        // Set minimum level to ERROR
        $this->logger->setMinLevel(LogLevel::ERROR, 'level-test');
        $logger = $this->logger->channel('level-test');

        // Act
        $logger->debug('Debug message');        // Should be filtered out
        $logger->info('Info message');          // Should be filtered out
        $logger->warning('Warning message');    // Should be filtered out
        $logger->error('Error message');        // Should be included
        $logger->critical('Critical message');  // Should be included

        // Assert
        rewind($stream);
        $output = stream_get_contents($stream);

        // These should NOT be in the output
        $this->assertStringNotContainsString('Debug message', $output);
        $this->assertStringNotContainsString('Info message', $output);
        $this->assertStringNotContainsString('Warning message', $output);

        // These SHOULD be in the output
        $this->assertStringContainsString('Error message', $output);
        $this->assertStringContainsString('Critical message', $output);

        fclose($stream);
    }

    public function testCompleteWorkflowWithMultipleHandlersAndProcessors(): void
    {
        // Arrange
        $stream1 = fopen('php://memory', 'w+');
        $stream2 = fopen('php://memory', 'w+');
        $handler1 = new StreamHandler($stream1);
        $handler2 = new StreamHandler($stream2);

        $processor1 = function (array $record): array {
            $record['extra']['processor1'] = 'value1';
            return $record;
        };

        $processor2 = function (array $record): array {
            $record['extra']['processor2'] = 'value2';
            return $record;
        };

        // Register channel with multiple handlers and processors
        $this->logger->register('complex', [$handler1, $handler2], [$processor1, $processor2]);

        // Act
        $logger = $this->logger->channel('complex');
        $logger->info('Complex test message', ['context' => 'test']);

        // Assert
        rewind($stream1);
        $output1 = stream_get_contents($stream1);
        $this->assertStringContainsString('Complex test message', $output1);
        $this->assertStringContainsString('complex', $output1);

        rewind($stream2);
        $output2 = stream_get_contents($stream2);
        $this->assertStringContainsString('Complex test message', $output2);
        $this->assertStringContainsString('complex', $output2);

        fclose($stream1);
        fclose($stream2);
    }

    public function testDynamicChannelCreationAndUsage(): void
    {
        // Arrange
        $stream = fopen('php://memory', 'w+');
        $handler = new StreamHandler($stream);

        // Start with a logger that has default configuration
        $logger = new Logger('dynamic-test', [$handler]);

        // Act - Create channels dynamically
        $apiLogger = $logger->channel('api');
        $dbLogger = $logger->channel('database');
        $cacheLogger = $logger->channel('cache');

        $apiLogger->info('API request processed');
        $dbLogger->warning('Database query slow');
        $cacheLogger->error('Cache miss');

        // Assert
        rewind($stream);
        $output = stream_get_contents($stream);

        $this->assertStringContainsString('API request processed', $output);
        $this->assertStringContainsString('Database query slow', $output);
        $this->assertStringContainsString('Cache miss', $output);

        // Verify channels were created
        $this->assertTrue($logger->hasChannel('api'));
        $this->assertTrue($logger->hasChannel('database'));
        $this->assertTrue($logger->hasChannel('cache'));

        fclose($stream);
    }

    public function testProcessorChainExecution(): void
    {
        // Arrange
        $stream = fopen('php://memory', 'w+');
        $handler = new StreamHandler($stream);

        $executionOrder = [];

        $processor1 = function (array $record) use (&$executionOrder): array {
            $executionOrder[] = 'processor1';
            $record['extra']['step1'] = 'executed';
            return $record;
        };

        $processor2 = function (array $record) use (&$executionOrder): array {
            $executionOrder[] = 'processor2';
            $record['extra']['step2'] = 'executed';
            return $record;
        };

        $processor3 = function (array $record) use (&$executionOrder): array {
            $executionOrder[] = 'processor3';
            $record['extra']['step3'] = 'executed';
            return $record;
        };

        $this->logger->register('chain-test', [$handler], [$processor1, $processor2, $processor3]);

        // Act
        $logger = $this->logger->channel('chain-test');
        $logger->info('Processor chain test');

        // Assert
        // Note: Processors are executed in reverse order (stack behavior)
        $this->assertEquals(['processor3', 'processor2', 'processor1'], $executionOrder);

        rewind($stream);
        $output = stream_get_contents($stream);
        $this->assertStringContainsString('Processor chain test', $output);

        fclose($stream);
    }

    public function testLoggerWithFileSystemIntegration(): void
    {
        // Arrange
        $logFile = sys_get_temp_dir() . '/integration_test.log';

        // Clean up any existing file
        if (file_exists($logFile)) {
            unlink($logFile);
        }

        $handler = new StreamHandler($logFile);
        $this->logger->register('file-test', [$handler]);

        // Act
        $logger = $this->logger->channel('file-test');
        $logger->info('File system integration test');
        $logger->warning('Warning message');
        $logger->error('Error message');

        // Assert
        $this->assertFileExists($logFile);
        $content = file_get_contents($logFile);

        $this->assertStringContainsString('File system integration test', $content);
        $this->assertStringContainsString('Warning message', $content);
        $this->assertStringContainsString('Error message', $content);
        $this->assertStringContainsString('file-test', $content);

        // Clean up
        unlink($logFile);
    }
}
