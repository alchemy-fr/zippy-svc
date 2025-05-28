<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Traits\CreatedAtDTOTrait;
use App\Api\Traits\UpdatedAtDTOTrait;
use DateTime;
use Symfony\Component\Serializer\Annotation\Groups;

class ArchiveOutput
{
    use CreatedAtDTOTrait;
    use UpdatedAtDTOTrait;


    #[Groups(["archive:read"])]
    private string $id;


    #[Groups(["archive:read"])]
    private string $identifier;


    #[Groups(["archive:read"])]
    private array $archives = [];

 
    #[Groups(["archive:read"])]
    private string $status;


    #[Groups(["archive:read"])]
    private ?string $downloadUrl = null;


    #[Groups(["archive:read"])]
    private ?DateTime $expiresAt = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getArchives(): array
    {
        return $this->archives;
    }

    public function setArchives(array $archives): void
    {
        $this->archives = $archives;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    public function setDownloadUrl(?string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    public function getExpiresAt(): ?DateTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
}
