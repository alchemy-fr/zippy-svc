<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Archive;
use App\Entity\File;

class ArchiveApiTest extends ApiTestCase
{
    public function testPostArchive(): void
    {
        $identifier = uniqid('test');
        $filePrefix = sprintf('file:///%s', __DIR__);
        $files = [
            [
                'url' => $filePrefix.'/three.jpg',
                'path' => 'one/two/three.jpg',
            ],
            [
                'url' => $filePrefix.'/four.txt',
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
        $this->assertRegExp('#^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$#', $json['id']);
        $this->assertEquals($identifier, $json['identifier']);
        $this->assertEquals('created', $json['status']);

        $archive = $this->getArchiveFromDatabase($json['id']);
        $this->expectedFiles($files, $archive);
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
                'url' => $f->getUrl(),
                'path' => $f->getPath(),
            ];
        }, $archive->getFiles()->getValues()));
    }

    public function testPutArchiveReturnsMethodNotAllowed(): void
    {
        $archive = $this->createArchive();

        $response = $this->request('PUT', '/archives/'.$archive->getId(), [
            'files' => [
                'url' => 'https://some-url.com/some/path',
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

        $filePrefix = sprintf('file:///%s', __DIR__);
        $files = [
            [
                'url' => $filePrefix.'/one/five.jpg',
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
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($this->getArchiveFromDatabase($archive->getId()));
    }
}
