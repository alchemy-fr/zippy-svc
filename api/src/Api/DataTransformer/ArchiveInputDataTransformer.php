<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use App\Api\ArchiveInput;
use App\Entity\Archive;

class ArchiveInputDataTransformer implements DataTransformerInterface
{
    /**
     * @param ArchiveInput $data
     */
    public function transform($data, string $to, array $context = [])
    {
        $isNew = !isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE]);
        $object = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? new Archive();

        if ($isNew) {
            $object->setIdentifier($data->getIdentifier());

            foreach ($data->getFiles() as $file) {
                $object->addFile($file);
            }
        } else {
            // TODO diff files

            foreach ($data->getFiles() as $file) {
                $object->addFile($file);
            }
        }

        return $object;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof Archive) {
            return false;
        }

        return Archive::class === $to && ArchiveInput::class === ($context['input']['class'] ?? null);
    }
}
