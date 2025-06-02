<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

use App\Archive\ArchiveManager;
use App\Entity\Archive;
use Arthem\Bundle\RabbitBundle\Consumer\Event\AbstractEntityManagerHandler;
use Arthem\Bundle\RabbitBundle\Consumer\Event\EventMessage;
use Arthem\Bundle\RabbitBundle\Consumer\Exception\ObjectNotFoundForHandlerException;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Throwable;

class BuildArchiveHandler extends AbstractEntityManagerHandler
{
    const EVENT = 'build_archive';

    private ArchiveManager $archiveManager;

    public function __construct(ArchiveManager $archiveManager)
    {
        $this->archiveManager = $archiveManager;
    }

    public function handle(EventMessage $message): void
    {
        $payload = $message->getPayload();
        $id = $payload['id'];

        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        $archive = $em->transactional(function () use ($em, $id): ?Archive {
            $archive = $em->find(Archive::class, $id, LockMode::PESSIMISTIC_WRITE);
            if (!$archive instanceof Archive) {
                throw new ObjectNotFoundForHandlerException(Archive::class, $id, __CLASS__);
            }

            if (Archive::STATUS_IN_PROGRESS === $archive->getStatus()) {
                return null;
            }

            $archive->setStatus(Archive::STATUS_IN_PROGRESS);
            $em->persist($archive);
            $em->flush();

            return $archive;
        });

        if (null === $archive) {
            return;
        }

        try {
            $this->archiveManager->buildArchive($archive);
        } catch (Throwable $e) {
            $archive->setStatus(Archive::STATUS_ERROR);
            $em->persist($archive);
            $em->flush();

            throw $e;
        }

        $archive->setStatus(Archive::STATUS_READY);
        $em->persist($archive);
        $em->flush();
    }

    public static function getHandledEvents(): array
    {
        return [self::EVENT];
    }
}
