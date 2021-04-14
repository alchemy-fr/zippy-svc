<?php

declare(strict_types=1);

namespace App\Archive;

use Alchemy\Zippy\Zippy;
use App\Download\DownloadManager;
use App\Entity\Archive;
use App\Utils\FilesystemUtils;
use Doctrine\ORM\EntityManagerInterface;

class ArchiveManager
{
    private EntityManagerInterface $em;
    private string $dataDir;
    private DownloadManager $downloadManager;

    public function __construct(EntityManagerInterface $em, string $dataDir, DownloadManager $downloadManager)
    {
        $this->em = $em;
        $this->dataDir = $dataDir;
        $this->downloadManager = $downloadManager;
    }

    public function buildArchive(Archive $archive): void
    {
        $dest = $this->dataDir.DIRECTORY_SEPARATOR.$archive->getId();
        try {
            if (is_dir($dest)) {
                FilesystemUtils::rrmdir($dest);
            }

            mkdir($dest, 0755, true);

            $this->downloadManager->downloadFiles($archive->getFiles(), $dest);

            $zippy = Zippy::load();
            $zippy->create($this->getArchivePath($archive), [
                'content' => $dest,
            ], true);
        } finally {
            FilesystemUtils::rrmdir($dest);
        }
    }

    public function getArchivePath(Archive $archive): string
    {
        return $this->dataDir.DIRECTORY_SEPARATOR.$archive->getId().'.zip';
    }

    public function deleteArchive(Archive $archive): void
    {
        $dir = $this->getArchiveDataDir($archive);
        if (is_dir($dir)) {
            FilesystemUtils::rrmdir($dir);
        }

        $this->em->remove($archive);
        $this->em->flush();
    }


    public function getArchive(string $id): ?Archive
    {
        return $this->em->find(Archive::class, $id);
    }

    private function getArchiveDataDir(Archive $archive): string
    {
        return $this->dataDir.DIRECTORY_SEPARATOR.$archive->getId();
    }
}
