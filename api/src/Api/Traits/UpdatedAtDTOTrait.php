<?php

declare(strict_types=1);

namespace App\Api\Traits;

use DateTime;
use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

trait UpdatedAtDTOTrait
{
    #[ApiProperty()]
    #[Groups(["_"])]
    protected DateTime $updatedAt;

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
