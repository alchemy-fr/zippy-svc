<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Archive;
use App\Entity\File;

abstract class AbstractZippyTestCase extends ApiTestCase
{
    protected function getTestDataSetDir(): string
    {
        return sprintf('file://%s', __DIR__.DIRECTORY_SEPARATOR.'files');
    }

    protected function getArchiveDir(): string
    {
        return self::$container->getParameter('app.archive_dir');
    }

    protected function removeArchive(string $id): void
    {
        $em = self::getEntityManager();
        $archive = $em->find(Archive::class, $id);
        if ($archive instanceof Archive) {
            $em->remove($archive);
            $em->flush();;
        }

        unlink($this->getArchiveDir().DIRECTORY_SEPARATOR.$id.'.zip');
    }

    protected function getArchiveFromDatabase(string $id): ?Archive
    {
        $em = self::getEntityManager();

        return $em->find(Archive::class, $id);
    }

    protected function expectedFiles(array $files, Archive $archive): void
    {
        $this->assertEquals($files, array_map(function (File $f): array {
            return [
                'uri' => $f->getUri(),
                'path' => $f->getPath(),
            ];
        }, $archive->getFiles()->getValues()));
    }

    protected function createArchive(array $options = []): Archive
    {
        $em = self::getEntityManager();

        $archive = new Archive();
        $archive->setClient('client');
        $archive->setIdentifier(uniqid('test'));

        $em->persist($archive);
        $em->flush();

        return $archive;
    }
}
