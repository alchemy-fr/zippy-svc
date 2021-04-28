<?php

declare(strict_types=1);

namespace App\Command;

use App\Consumer\Handler\CleanOldArchivesHandler;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanOldArchivesCommand extends Command
{
    use SubCommandTrait;

    const COMMAND_NAME = 'app:archives:clean-old';

    private EventProducer $eventProducer;

    public function __construct(EventProducer $eventProducer, string $name = null)
    {
        parent::__construct($name);
        $this->eventProducer = $eventProducer;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventProducer->publish(new EventMessage(CleanOldArchivesHandler::EVENT, []));

        $output->writeln('Clean triggered!');

        return 0;
    }
}
