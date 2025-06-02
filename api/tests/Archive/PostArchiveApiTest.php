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
        $this->doTestPostArchiveOK($files);
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
        $this->doTestPostArchiveOK($files, ['identifier' => 'my_unique_id']);
    }

    public function testPostArchiveWithExpirationOK(): void
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
        $this->doTestPostArchiveOK($files, ['expiresIn' => 300]);
    }

    private function doTestPostArchiveOK(array $files, array $options = []): void
    {
        $data = [
            'files' => $files,
        ];
        if ($options['identifier'] ?? false) {
            $data['identifier'] = $options['identifier'];
        }
        if ($options['expiresIn'] ?? false) {
            $data['expiresIn'] = $options['expiresIn'];
        }
        $now = time();
        $response = $this->request('POST', '/archives', $data);

        $json = json_decode($response->getContent(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('id', $json);
        $id = $json['id'];
        $this->assertRegExp('#^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$#', $id);
        if ($options['identifier'] ?? false) {
            $this->assertEquals($options['identifier'], $json['identifier']);
        }
        if ($options['expiresIn'] ?? false) {
            $dateTs = (new \DateTime($json['expiresAt']))->getTimestamp();
            $this->assertGreaterThanOrEqual($now + $options['expiresIn'], $dateTs);
            // let 5s delta in the worst scenario
            $this->assertLessThan($now + $options['expiresIn'] + 5, $dateTs);
        } else {
            $this->assertNull($json['expiresAt']);
        }
        $this->assertEquals('created', $json['status']);
        $this->assertArrayHasKey('downloadUrl', $json);
        $this->assertMatchesRegularExpression(sprintf('#^http://localhost/archives/%s/download\?jwt=.+$#', $id), $json['downloadUrl']);

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
