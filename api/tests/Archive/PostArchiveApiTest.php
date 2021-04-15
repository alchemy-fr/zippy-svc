<?php

declare(strict_types=1);

namespace App\Tests\Archive;

use App\Tests\AbstractZippyTestCase;

class PostArchiveApiTest extends AbstractZippyTestCase
{
    public function testPostArchiveOK(): void
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

    public function testPostArchive422(): void
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
            'files' => $files,
        ]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals('application/problem+json; charset=utf-8', $response->headers->get('Content-Type'));
    }
}
