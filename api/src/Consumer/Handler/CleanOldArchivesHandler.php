<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

use App\Archive\ArchiveManager;
use App\Entity\Archive;
use Arthem\Bundle\RabbitBundle\Consumer\Event\AbstractEntityManagerHandler;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\ObjectNotFoundForHandlerException;
use Arthem\Bundle\RabbitBundle\Producer\EventProducer;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Throwable;

class CleanOldArchivesHandler extends AbstractEntityManagerHandler
{
    const EVENT = 'clean_archives';

    private EventProducer $eventProducer;

    public function __construct(EventProducer $eventProducer)
    {
        $this->eventProducer = $eventProducer;
    }

    public function handle(EventMessage $message): void
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        $archives = $em->getRepository(Archive::class)->getAllIterator();

        foreach ($archives as $archive) {
            $this->eventProducer->publish(new EventMessage(DeleteArchiveHandler::EVENT, [
                'id' => $archive->getId(),
            ]));
        }
    }

    public static function getHandledEvents(): array
    {
        return [self::EVENT];
    }
}
