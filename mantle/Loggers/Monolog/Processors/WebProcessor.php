<?php

declare(strict_types=1);

namespace Mantle\Loggers\Monolog\Processors;

use ArrayAccess;
use Monolog\Processor\WebProcessor as MonologWebProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;

class WebProcessor implements ProcessorInterface
{
    private MonologWebProcessor $processor;

    /**
     * @param null|array<string,mixed>|ArrayAccess<string,mixed> $serverData
     * @param null|array<string>|array<string,string> $extraFields
     */
    public function __construct(
        array|ArrayAccess|null $serverData = null,
        array|null $extraFields = null
    ) {
        $this->processor = new MonologWebProcessor(
            $serverData,
            $extraFields
        );
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
