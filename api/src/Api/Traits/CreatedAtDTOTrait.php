<?php

declare(strict_types=1);

namespace App\Api\Traits;

use DateTime;
use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedAtDTOTrait
{
     #[ApiProperty()]
     #[Groups(["_"])]
    protected DateTime $createdAt;

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
