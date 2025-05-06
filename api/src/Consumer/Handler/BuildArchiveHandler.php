<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

use Throwable;
use App\Entity\Archive;
use Doctrine\DBAL\LockMode;
use App\Archive\ArchiveManager;
use App\Consumer\Handler\BuildMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsMessageHandler]
class BuildArchiveHandler
{
    private ArchiveManager $archiveManager;
    private EntityManagerInterface $em;

    public function __construct(ArchiveManager $archiveManager, EntityManagerInterface $em)
    {
        $this->archiveManager = $archiveManager;
        $this->em = $em;
    }

    public function __invoke(BuildMessage $message): void
    {
        $payload = $message->getPayload();
        $id = $payload['id'];

        $archive = $this->em->transactional(function () use ($id): ?Archive {
            $archive = $this->em->find(Archive::class, $id, LockMode::PESSIMISTIC_WRITE);
            if (!$archive instanceof Archive) {
                throw new NotFoundHttpException("Archive not found");
            }

            if (Archive::STATUS_IN_PROGRESS === $archive->getStatus()) {
                return null;
            }

            $archive->setStatus(Archive::STATUS_IN_PROGRESS);
            $this->em->persist($archive);
            $this->em->flush();

            return $archive;
        });

        if (null === $archive) {
            return;
        }

        try {
            $this->archiveManager->buildArchive($archive);
        } catch (Throwable $e) {
            $archive->setStatus(Archive::STATUS_ERROR);
            $this->em->persist($archive);
            $this->em->flush();

            throw $e;
        }

        $archive->setStatus(Archive::STATUS_READY);
        $this->em->persist($archive);
        $this->em->flush();
    }
}
