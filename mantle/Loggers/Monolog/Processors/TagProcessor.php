<?php

declare(strict_types=1);

namespace Rogue\Mantle\Loggers\Monolog\Processors;

use Monolog\Processor\TagProcessor as MonologTagProcessor;
use Monolog\Processor\ProcessorInterface as MonologProcessorInterface;

class TagProcessor implements ProcessorInterface
{
    private MonologTagProcessor $processor;

    /**
     * @param string[] $tags
     */
    public function __construct(array $tags = [])
    {
        $this->processor = new MonologTagProcessor($tags);
    }

    public function getProcessor(): MonologProcessorInterface|callable
    {
        return $this->processor;
    }
}
