<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

use App\Entity\Archive;
use App\Archive\ArchiveManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsMessageHandler]
class DeleteArchiveHandler
{
    public function __construct(private ArchiveManager $archiveManager,
        private EntityManagerInterface $em)
    {
    }

    public function __invoke(DeleteArchive $message): void
    {
        $id = $message->getId();

        $archive = $this->em->find(Archive::class, $id);
        if (!$archive instanceof Archive) {
            throw new NotFoundHttpException("Archive not found");
        }

        $this->archiveManager->deleteArchive($archive);
    }
}
