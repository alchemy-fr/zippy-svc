<?php

declare(strict_types=1);

namespace App\Tests;

class ArchiveApiTest extends AbstractZippyTestCase
{
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
            'downloadFilename' => 'foo',
        ]);

        $json = json_decode($response->getContent(), true);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->getHeaders()['content-type'][0]);

        $this->assertArrayHasKey('id', $json);
        $id = $json['id'];
        $this->assertMatchesRegularExpression('#^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$#', $id);
        $this->assertEquals($identifier, $json['identifier']);
        $this->assertEquals('created', $json['status']);
        $this->assertArrayHasKey('downloadUrl', $json);
        $this->assertMatchesRegularExpression(sprintf('#^http://localhost/archives/%s/download\?jwt=.+$#', $id), $json['downloadUrl']);

        $archive = $this->getArchiveFromDatabase($id);
        $this->expectedFiles($files, $archive);
    }

    public function testPutArchiveReturnsMethodNotAllowed(): void
    {
        $archive = $this->createArchive();

        $response = static::createClient()->request('PUT', '/archives/'.$archive->getId(), [
            'json' => [
                'files' => [
                    'uri' => 'https://some-url.com/some/path',
                    'path' => 'one/five.jpg',
                ],
            ]           
        ]);

        $this->assertEquals(405, $response->getStatusCode());
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

        $response = static::createClient()->request('PATCH', '/archives/'.$archive->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Authorization' => 'client:secret'
            ],
            'json' => [
                'files' => $files,
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $archive = $this->getArchiveFromDatabase($archive->getId());
        $this->expectedFiles($files, $archive);
    }

    public function testDeleteArchive(): void
    {
        $archive = $this->createArchive();

        $response = static::createClient()->request('DELETE', '/archives/'.$archive->getId(), [
            'headers' => [
                'Authorization' => 'client:secret'
            ]
        ]);

        $this->clearEmBeforeApiCall();
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertNull($this->getArchiveFromDatabase($archive->getId()));
    }
}
