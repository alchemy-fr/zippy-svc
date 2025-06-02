<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

use App\Archive\ArchiveManager;
use App\Entity\Archive;
use Arthem\Bundle\RabbitBundle\Consumer\Event\AbstractEntityManagerHandler;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\ObjectNotFoundForHandlerException;

class DeleteArchiveHandler extends AbstractEntityManagerHandler
{
    const EVENT = 'delete_archive';

    private ArchiveManager $archiveManager;

    public function __construct(ArchiveManager $archiveManager)
    {
        $this->archiveManager = $archiveManager;
    }

    public function handle(EventMessage $message): void
    {
        $payload = $message->getPayload();
        $id = $payload['id'];

        $em = $this->getEntityManager();
        $archive = $em->find(Archive::class, $id);
        if (!$archive instanceof Archive) {
            throw new ObjectNotFoundForHandlerException(Archive::class, $id, __CLASS__);
        }

        $this->archiveManager->deleteArchive($archive);
    }

    public static function getHandledEvents(): array
    {
        return [self::EVENT];
    }
}
