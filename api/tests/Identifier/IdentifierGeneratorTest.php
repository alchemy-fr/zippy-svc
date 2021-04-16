<?php

declare(strict_types=1);

namespace App\Tests\Identifier;

use App\Archive\IdentifierGenerator;
use PHPUnit\Framework\TestCase;

class IdentifierGeneratorTest extends TestCase
{
    /**
     * @dataProvider getSameCases
     */
    public function testGeneratorWillReturnSameIdentifier(array $files1, array $files2): void
    {
        $generator = new IdentifierGenerator();

        $this->assertEquals(
            $generator->generateIdentifier($files1),
            $generator->generateIdentifier($files2),
            'Identifier should be the same'
        );
    }

    /**
     * @dataProvider getDifferentCases
     */
    public function testGeneratorWillReturnDifferentIdentifier(array $files1, array $files2): void
    {
        $generator = new IdentifierGenerator();

        $this->assertNotEquals(
            $generator->generateIdentifier($files1),
            $generator->generateIdentifier($files2),
            'Identifier should be different'
        );
    }

    public function getSameCases(): array
    {
        return [
            // Exactly same
            [
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
            ],
            // Different file order
            [
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
                [
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                ],
            ],
            // Different properties order
            [
                [
                    [
                        'path' => '1.jpg',
                        'uri' => 'http://foo/1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'path' => '2.jpg',
                        'uri' => 'http://foo/2.jpg',
                    ],
                ],
            ],
        ];
    }

    public function getDifferentCases(): array
    {
        return [
            [
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => 'different-path.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
            ],
            [
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/2.jpg',
                        'path' => '2.jpg',
                    ],
                ],
                [
                    [
                        'uri' => 'http://foo/1.jpg',
                        'path' => '1.jpg',
                    ],
                    [
                        'uri' => 'http://foo/different-uri.jpg',
                        'path' => '2.jpg',
                    ],
                ],
            ],
        ];
    }
}
