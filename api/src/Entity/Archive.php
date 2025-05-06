<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Ramsey\Uuid\Uuid;
use App\Api\ArchiveInput;
use App\Api\ArchiveOutput;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Ramsey\Uuid\UuidInterface;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidType;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ArchiveRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Api\DataTransformer\ArchiveInputProcessor;
use App\Api\DataTransformer\ArchiveOutputProvider;

#[ApiResource(
   shortName: "archive",
   security: "is_granted('ROLE_API')",
   normalizationContext: [
    "groups" => ["_", "archive:read"],
    "skip_null_values" => false,
   ],
   denormalizationContext: ["groups"=> ["archive:write"]],
   input: ArchiveInput::class,
   output: ArchiveOutput::class,
   operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Patch(),
        new Delete()
   ],
   processor: ArchiveInputProcessor::class,
   provider: ArchiveOutputProvider::class
  )]
#[ORM\Entity(repositoryClass: ArchiveRepository::class)]
#[ORM\Table]
class Archive
{
    public const STATUS_CREATED = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_READY = 2;
    public const STATUS_UPDATING = 3;
    public const STATUS_ERROR = 4;

    private const STATUSES_LABELS = [
        self::STATUS_CREATED => 'created',
        self::STATUS_IN_PROGRESS => 'in_progress',
        self::STATUS_READY => 'ready',
        self::STATUS_UPDATING => 'updating',
        self::STATUS_ERROR => 'error',
    ];
   
    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private UuidInterface $id;


    #[ORM\Column(type: "string", length: 128, nullable: false)]
    private ?string $client = null;


    #[ORM\Column(type: "datetime")]
    #[Gedmo\Timestampable(on: "create")]
    private ?DateTime $createdAt = null;

    #[ORM\Column(type: "datetime")]
    #[Gedmo\Timestampable(on: "update")]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTime $expiresAt = null;


    #[ORM\Column(type: "string", length: 128, nullable: false, unique: true)]
    private ?string $identifier = null;

    /**
     * @var Collection|File[]|null
     */
    #[ORM\OneToMany(targetEntity: "File", mappedBy: "archive", cascade: ["persist", "remove"])]
    private ?Collection $files = null;


    #[ORM\Column(type: "smallint", nullable: false)]
    private int $status = self::STATUS_CREATED;


    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $downloadFilename = null;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->files = new ArrayCollection();
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

    public function hasError(): bool
    {
        return self::STATUS_ERROR === $this->getStatus();
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

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): void
    {
        $this->client = $client;
    }

    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
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
