<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Api\ArchiveOutput;
use App\Entity\Archive;

class ArchiveOutputDataTransformer implements DataTransformerInterface
{
    /**
     * @param Archive $object
     */
    public function transform($object, string $to, array $context = [])
    {
        $output = new ArchiveOutput();
        $output->setCreatedAt($object->getCreatedAt());
        $output->setUpdatedAt($object->getUpdatedAt());
        $output->setId($object->getId());
        $output->setIdentifier($object->getIdentifier());
        $output->setStatus($object->getStatusLabel());

        return $output;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return ArchiveOutput::class === $to && $data instanceof Archive;
    }
}
