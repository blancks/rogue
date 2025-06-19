<?php

namespace Rogue\Mantle\Console;

interface CommandInterface
{
    /**
     * @param string[] $args
     */
    public function run(array $args = []): int;

    public function getDescription(): string;
}
