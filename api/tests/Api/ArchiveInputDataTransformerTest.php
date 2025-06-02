<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Api\ArchiveInput;
use App\Api\DataTransformer\ArchiveInputDataTransformer;
use App\Archive\IdentifierGenerator;
use App\Entity\Archive;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ArchiveInputDataTransformerTest extends TestCase
{
    public function testMaximumExpirationWillRaiseBadRequest(): void
    {
        $transformer = $this->createTransformer(3600);
        $input = new ArchiveInput();
        $input->setExpiresIn(3601);

        $this->expectException(BadRequestHttpException::class);
        $transformer->transform($input, Archive::class, []);
    }

    public function testUnderMaximumExpirationWillBeFine(): void
    {
        $transformer = $this->createTransformer(3600);
        $input = new ArchiveInput();
        $input->setExpiresIn(3599);

        $archive = $transformer->transform($input, Archive::class, []);
        $this->assertNotNull($archive->getExpiresAt());
    }

    public function testMaximumExpirationWillBeApplied(): void
    {
        $transformer = $this->createTransformer(3600);
        $input = new ArchiveInput();

        $archive = $transformer->transform($input, Archive::class, []);
        $this->assertNotNull($archive->getExpiresAt());
    }

    public function testNoMaximumExpirationWillBeApplied(): void
    {
        $transformer = $this->createTransformer(-1);
        $input = new ArchiveInput();

        $archive = $transformer->transform($input, Archive::class, []);
        $this->assertNull($archive->getExpiresAt());
    }

    private function createTransformer(int $maxExpirationTime): ArchiveInputDataTransformer
    {
        /** @var ValidatorInterface $validator */
        $validator = $this->createMock(ValidatorInterface::class);
        /** @var IdentifierGenerator $identifierGenerator */
        $identifierGenerator = $this->createMock(IdentifierGenerator::class);

        return new ArchiveInputDataTransformer(
            $validator,
            $identifierGenerator,
            $maxExpirationTime
        );
    }
}
