<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  shortName="file",
 * )
 * @ORM\Entity
 */
final class File
{
    /**
     * @ApiProperty(identifier=true)
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $id;

    /**
     * @ORM\ManyToOne(targetEntity="Archive", inversedBy="files")
     */
    private Archive $archive;

    /**
     * The remote file URL.
     *
     * @Groups({"file:read", "file:write", "archive:write"})
     * @ORM\Column(type="string", length=1024)
     */
    private ?string $url = null;

    /**
     * The path in the archive.
     *
     * @Groups({"file:read", "file:write", "archive:write"})
     * @ORM\Column(type="string", length=1024)
     */
    private ?string $path = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): string
    {
        return $this->id->__toString();
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getArchive(): Archive
    {
        return $this->archive;
    }

    public function setArchive(Archive $archive): void
    {
        $this->archive = $archive;
    }
}
