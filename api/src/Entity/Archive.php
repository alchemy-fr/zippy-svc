<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DateTime;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use App\Api\ArchiveInput;
use App\Api\ArchiveOutput;

/**
 * @ApiResource(
 *  shortName="archive",
 *  normalizationContext={"groups"={"_", "archive:read"}},
 *  denormalizationContext={"groups"={"archive:write"}},
 *  input=ArchiveInput::class,
 *  output=ArchiveOutput::class,
 *  itemOperations={
 *    "get",
 *    "patch",
 *    "delete",
 *  }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ArchiveRepository")
 */
class Archive
{
    public const STATUS_CREATED = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_READY = 2;
    public const STATUS_UPDATING = 3;
    private const STATUSES_LABELS = [
        self::STATUS_CREATED => 'created',
        self::STATUS_IN_PROGRESS => 'in_progress',
        self::STATUS_READY => 'ready',
        self::STATUS_UPDATING => 'updating',
    ];

    /**
     * @ApiProperty(identifier=true)
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private ?DateTime $updatedAt = null;

    /**
     * @ORM\Column(type="string", length=128, nullable=false, unique=true)
     */
    private ?string $identifier = null;

    /**
     * @var Collection|File[]|null
     *
     * @ApiSubresource()
     * @ORM\OneToMany(targetEntity="File", mappedBy="archive", cascade={"persist", "remove"})
     */
    private ?Collection $files = null;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    private int $status = self::STATUS_CREATED;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->files  = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id->__toString();
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function isReady(): bool
    {
        return self::STATUS_READY === $this->getStatus();
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES_LABELS[$this->getStatus()];
    }

    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): void
    {
        $file->setArchive($this);
        $this->files->add($file);
    }

    public function removeFile(File $file): void
    {
        $this->files->removeElement($file);
    }
}
