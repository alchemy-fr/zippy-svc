<?php

declare(strict_types=1);

namespace App\Command;

use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

trait SubCommandTrait
{
    protected function runSubCommand(OutputInterface $output, string $commandName, array $args = [], bool $interactive = false)
    {
        $output->writeln(sprintf('<info>⬤</info> Run command <comment>%s</comment>', $commandName));
        $subCommand = $this->getApplication()->find($commandName);
        $arguments = array_merge(['command' => $commandName], $args);
        $input = new ArrayInput($arguments);
        $input->setInteractive($interactive);

        $returnCode = $subCommand->run($input, $output);

        if (0 !== $returnCode) {
            throw new Exception(sprintf('Sub command "%s" failed (returned code %d)', $commandName, $returnCode));
        }
    }
}
