<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    use SubCommandTrait;

    const COMMAND_NAME = 'app:setup';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runSubCommand($output, 'doctrine:database:create');
        $this->runSubCommand($output, 'doctrine:migration:migrate');

        return 0;
    }
}
