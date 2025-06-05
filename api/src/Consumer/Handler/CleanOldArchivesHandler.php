<?php

declare(strict_types=1);

namespace App\Consumer\Handler;

use App\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CleanOldArchivesHandler
{
    public function __construct(private MessageBusInterface $bus, 
        private EntityManagerInterface $em)
    {

    }

    public function __invoke(CleanOldArchives $message): void
    {
        $archives = $this->em->getRepository(Archive::class)->getExpired();

        foreach ($archives as $archive) {
            $this->bus->dispatch(new DeleteArchive($archive->getId()));
        }
    }
}
