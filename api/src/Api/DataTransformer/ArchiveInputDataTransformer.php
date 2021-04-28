<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Api\ArchiveInput;
use App\Archive\IdentifierGenerator;
use App\Entity\Archive;
use DateTime;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ArchiveInputDataTransformer implements DataTransformerInterface
{
    private ValidatorInterface $validator;
    private IdentifierGenerator $identifierGenerator;
    private ?int $maxExpirationTime;

    public function __construct(
        ValidatorInterface $validator,
        IdentifierGenerator $identifierGenerator,
        ?int $maxExpirationTime
    )
    {
        $this->validator = $validator;
        $this->identifierGenerator = $identifierGenerator;
        $this->maxExpirationTime = $maxExpirationTime;
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
            ],
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

        $hasMaxExpiration = $this->maxExpirationTime >= 0;

        if ($data->getExpiresIn()) {
            if ($hasMaxExpiration && $data->getExpiresIn() > $this->maxExpirationTime) {
                throw new BadRequestHttpException(sprintf('Expiration must not exceed %d seconds', $this->maxExpirationTime));
            }

            $expiresAt = new DateTime();
            $expiresAt->setTimestamp(time() + $data->getExpiresIn());

            $object->setExpiresAt($expiresAt);
        } elseif ($hasMaxExpiration) {
            $expiresAt = new DateTime();
            $expiresAt->setTimestamp(time() + $this->maxExpirationTime);

            $object->setExpiresAt($expiresAt);
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
