<?php

declare(strict_types=1);

namespace App\Command;

use App\Consumer\Handler\CleanOldArchives;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanOldArchivesCommand extends Command
{
    use SubCommandTrait;

    const COMMAND_NAME = 'app:archives:clean-old';

    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus, string $name = null)
    {
        parent::__construct($name);
        $this->bus = $bus;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bus->dispatch(new CleanOldArchives());

        $output->writeln('Clean triggered!');

        return 0;
    }
}
