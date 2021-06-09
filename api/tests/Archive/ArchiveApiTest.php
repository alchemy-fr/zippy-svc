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
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('id', $json);
        $id = $json['id'];
        $this->assertRegExp('#^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$#', $id);
        $this->assertEquals($identifier, $json['identifier']);
        $this->assertEquals('created', $json['status']);
        $this->assertArrayHasKey('downloadUrl', $json);
        $this->assertMatchesRegularExpression(sprintf('#^http://localhost/archives/%s/download\?jwt=.+$#', $id), $json['downloadUrl']);

        $archive = $this->getArchiveFromDatabase($id);
        $this->expectedFiles($files, $archive);

        $archivePath = $this->getArchiveDir().DIRECTORY_SEPARATOR.$id.'.zip';
        $this->assertTrue(file_exists($archivePath));

        $response = $this->request('GET', $json['downloadUrl']);
        $html = $response->getContent();
        if (1 !== preg_match('#document\.location = \'([^\']+)\'#', $html, $matches)) {
            throw new \Exception('Cannot find redirect location in HTML');
        }
        $downloadUrl = $matches[1];
        $response = $this->request('GET', $downloadUrl);
        $this->assertEquals('attachment; filename="foo.zip"', $response->headers->get('Content-Disposition'));

        $this->removeArchive($id);
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
}
