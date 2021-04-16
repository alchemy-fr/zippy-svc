<?php

declare(strict_types=1);

namespace App\Tests\Archive;

use App\Tests\AbstractZippyTestCase;

class PostArchiveApiTest extends AbstractZippyTestCase
{
    public function testPostArchiveOK(): void
    {
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
        $this->doTestPostArchiveOK($files, null);
    }

    public function testPostArchiveWithDefinedIdentifierOK(): void
    {
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
        $this->doTestPostArchiveOK($files, uniqid('test'));
    }

    private function doTestPostArchiveOK(array $files, ?string $identifier): void
    {
        $data = [
            'files' => $files,
        ];
        if (null !== $identifier) {
            $data['identifier'] = $identifier;
        }
        $response = $this->request('POST', '/archives', $data);

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('id', $json);
        $id = $json['id'];
        $this->assertRegExp('#^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$#', $id);
        if (null !== $identifier) {
            $this->assertEquals($identifier, $json['identifier']);
        }
        $this->assertEquals('created', $json['status']);
        $this->assertArrayHasKey('downloadUrl', $json);
        $this->assertEquals(sprintf('http://localhost/archives/%s/download', $id), $json['downloadUrl']);

        $archive = $this->getArchiveFromDatabase($id);
        $this->expectedFiles($files, $archive);

        $archivePath = $this->getArchiveDir().DIRECTORY_SEPARATOR.$id.'.zip';
        $this->assertTrue(file_exists($archivePath));

        $this->removeArchive($id);
    }

    public function testPostArchiveWithEmptyIdentifierReturns422(): void
    {
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
            'identifier' => '',
            'files' => $files,
        ]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function testPostArchiveWithEmptyFilesReturns422(): void
    {
        $response = $this->request('POST', '/archives', [
            'files' => [],
        ]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
    }
}
