<?php

declare(strict_types=1);

namespace App\Api;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\Entity\File;
use Symfony\Component\Serializer\Annotation\Groups;

final class ArchiveInput
{
    /**
     * The external unique identifier.
     * It should include consumer service name to avoid conflicts.
     *
     * @Groups({"archive:write"})
     */
    private ?string $identifier = null;

    /**
     * The archive TTL in seconds.
     *
     * @Groups({"archive:write"})
     */
    private ?int $expiresIn = null;

    /**
     * @var File[]
     *
     * @ApiProperty(readableLink=true)
     * @Groups({"archive:write"})
     */
    private array $files = [];

    /**
     * The filename without extension.
     *
     * @Groups({"archive:write"})
     */
    private ?string $downloadFilename = null;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(?int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    public function getDownloadFilename(): ?string
    {
        return $this->downloadFilename;
    }

    public function setDownloadFilename(?string $downloadFilename): void
    {
        $this->downloadFilename = $downloadFilename;
    }
}
