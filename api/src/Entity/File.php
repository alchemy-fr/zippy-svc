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
 *  collectionOperations={},
 *  itemOperations={},
 * )
 * @ORM\Entity
 */
class File
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
    private ?string $uri = null;

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

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
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
