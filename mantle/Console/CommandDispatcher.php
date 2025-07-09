<?php

namespace Mantle\Console;

class CommandDispatcher
{
    /** @var array<string, CommandInterface> */
    protected array $commands = [];

    public function register(string $name, CommandInterface $command): void
    {
        $this->commands[$name] = $command;
    }

    /**
     * @param string $name
     * @param string[] $args
     */
    public function run(string $name, array $args = []): void
    {
        if (!isset($this->commands[$name])) {
            echo "Unknown command: $name\n";
            $this->printAvailableCommands();
            exit(1);
        }
        $exitCode = $this->commands[$name]->run($args);
        exit($exitCode);
    }

    public function printAvailableCommands(): void
    {
        echo "Available commands:\n";
        foreach ($this->commands as $name => $command) {
            echo "  $name\t" . $command->getDescription() . "\n";
        }
    }
}
