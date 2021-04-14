<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Archive;
use App\Entity\File;

class ArchiveApiTest extends ApiTestCase
{
    private function getTestDataSetDir(): string
    {
        return sprintf('file://%s', __DIR__.DIRECTORY_SEPARATOR.'files');
    }

    private function getArchiveDir(): string
    {
        return self::$container->getParameter('app.archive_dir');
    }

    public function testPostArchive(): void
    {
        $identifier = uniqid('test');
        $filePrefix = $this->getTestDataSetDir();
        $files = [
            [
                'uri' => $filePrefix.'/three.jpg',
                'path' => 'one/two/three.jpg',
            ],
            [
                'uri' => $filePrefix.'/four.txt',
                'path' => 'four.txt',
            ],
        ];
        $response = $this->request('POST', '/archives', [
            'identifier' => $identifier,
            'files' => $files,
        ]);

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('id', $json);
        $id = $json['id'];
        $this->assertRegExp('#^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$#', $id);
        $this->assertEquals($identifier, $json['identifier']);
        $this->assertEquals('created', $json['status']);
        $this->assertArrayHasKey('downloadUrl', $json);
        $this->assertEquals(sprintf('http://localhost/archives/%s/download', $id), $json['downloadUrl']);

        $archive = $this->getArchiveFromDatabase($id);
        $this->expectedFiles($files, $archive);

        $archivePath = $this->getArchiveDir().DIRECTORY_SEPARATOR.$id.'.zip';
        $this->assertTrue(file_exists($archivePath));

        $this->removeArchive($id);
    }

    private function removeArchive(string $id): void
    {
        unlink($this->getArchiveDir().DIRECTORY_SEPARATOR.$id.'.zip');
    }

    private function getArchiveFromDatabase(string $id): ?Archive
    {
        $em = self::getEntityManager();

        return $em->find(Archive::class, $id);
    }

    private function expectedFiles(array $files, Archive $archive): void
    {
        $this->assertEquals($files, array_map(function (File $f): array {
            return [
                'uri' => $f->getUri(),
                'path' => $f->getPath(),
            ];
        }, $archive->getFiles()->getValues()));
    }

    public function testPutArchiveReturnsMethodNotAllowed(): void
    {
        $archive = $this->createArchive();

        $response = $this->request('PUT', '/archives/'.$archive->getId(), [
            'files' => [
                'uri' => 'https://some-url.com/some/path',
                'path' => 'one/five.jpg',
            ],
        ]);

        $this->assertEquals(405, $response->getStatusCode());
    }

    private function createArchive(array $options = []): Archive
    {
        $em = self::getEntityManager();

        $archive = new Archive();
        $archive->setIdentifier(uniqid('test'));

        $em->persist($archive);
        $em->flush();

        return $archive;
    }

    public function testPatchArchive(): void
    {
        $archive = $this->createArchive();

        $filePrefix = $this->getTestDataSetDir();
        $files = [
            [
                'uri' => $filePrefix.'/one/five.jpg',
                'path' => 'one/five.jpg',
            ],
        ];

        $response = $this->request('PATCH', '/archives/'.$archive->getId(), [
            'files' => $files,
        ], [], [
            'CONTENT_TYPE' => 'application/merge-patch+json',
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $archive = $this->getArchiveFromDatabase($archive->getId());
        $this->expectedFiles($files, $archive);
    }

    public function testDeleteArchive(): void
    {
        $archive = $this->createArchive();

        $response = $this->request('DELETE', '/archives/'.$archive->getId());

        $this->clearEmBeforeApiCall();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($this->getArchiveFromDatabase($archive->getId()));
    }

    protected function clearEmBeforeApiCall(): void
    {
        self::getEntityManager()->clear();
    }
}
