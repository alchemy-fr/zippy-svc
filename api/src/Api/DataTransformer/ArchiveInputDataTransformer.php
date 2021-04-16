<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Api\ArchiveInput;
use App\Archive\IdentifierGenerator;
use App\Entity\Archive;

class ArchiveInputDataTransformer implements DataTransformerInterface
{
    private ValidatorInterface $validator;
    private IdentifierGenerator $identifierGenerator;

    public function __construct(ValidatorInterface $validator, IdentifierGenerator $identifierGenerator)
    {
        $this->validator = $validator;
        $this->identifierGenerator = $identifierGenerator;
    }

    /**
     * @param ArchiveInput $data
     */
    public function transform($data, string $to, array $context = [])
    {
        $isNew = !isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE]);

        $this->validator->validate($data, [
            'groups' => [
                'Default',
                $isNew ? 'Post' : 'Patch',
            ]
        ]);

        $object = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? new Archive();

        if ($isNew) {
            $identifier = $data->getIdentifier() ?? $this->identifierGenerator->generateIdentifier($data->getFiles());
            $object->setIdentifier($identifier);

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
