<?php

declare(strict_types=1);

namespace Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\PsrLogMessageProcessor as MonologPsrLogMessageProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;

class PsrLogMessageProcessor implements ProcessorInterface
{
    private MonologPsrLogMessageProcessor $processor;

    /**
     * @param string|null $dateFormat The format of the timestamp: one supported by DateTime::format
     * @param bool $removeUsedContextFields If set to true the fields interpolated into message gets unset
     */
    public function __construct(
        ?string $dateFormat = null,
        bool $removeUsedContextFields = false
    ) {
        $this->processor = new MonologPsrLogMessageProcessor(
            $dateFormat,
            $removeUsedContextFields
        );
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
