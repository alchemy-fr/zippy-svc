<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use DateTime;
use App\Entity\Archive;
use ApiPlatform\Metadata\Operation;
use App\Archive\IdentifierGenerator;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use ApiPlatform\Serializer\AbstractItemNormalizer;
use Alchemy\Zippy\Archive\Archive as ArchiveArchive;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ArchiveInputProcessor implements ProcessorInterface
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

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Archive
    {
        $isNew = !isset($context[AbstractItemNormalizer::OBJECT_TO_POPULATE]);

        $this->validator->validate($data, [
            'groups' => [
                'Default',
                $isNew ? 'Post' : 'Patch',
            ],
        ]);

        $object = $context[AbstractItemNormalizer::OBJECT_TO_POPULATE] ?? new Archive();

        if (null !== $data->getDownloadFilename()) {
            $object->setDownloadFilename($data->getDownloadFilename());
        }

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
}
