<?php

namespace Rogue\Mantle\Console\Commands;

use Rogue\Mantle\Console\CommandInterface;

class HelloCommand implements CommandInterface
{
    /**
     * @param string[] $args
     */
    public function run(array $args = []): int
    {
        $name = $args[0] ?? 'World';
        echo "Hello, $name!\n";
        return 0;
    }

    public function getDescription(): string
    {
        return 'Prints Hello, <name>!';
    }
}
