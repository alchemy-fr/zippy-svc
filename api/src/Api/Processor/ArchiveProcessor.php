<?php

declare(strict_types=1);

namespace App\Api\Processor;

use DateTime;
use App\Entity\Archive;
use ApiPlatform\Metadata\Operation;
use App\Archive\IdentifierGenerator;
use App\Consumer\Handler\BuildArchive;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Validator\ValidatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ArchiveProcessor implements ProcessorInterface
{
    private ValidatorInterface $validator;
    private IdentifierGenerator $identifierGenerator;
    private ?int $maxExpirationTime;
    private Security $security;
    private MessageBusInterface $bus;
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        IdentifierGenerator $identifierGenerator,
        Security $security,
        MessageBusInterface $bus,
        ?int $maxExpirationTime
    )
    {
        $this->validator = $validator;
        $this->identifierGenerator = $identifierGenerator;
        $this->maxExpirationTime = $maxExpirationTime;
        $this->security = $security;
        $this->bus = $bus;
        $this->em = $em;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Archive
    {

        $isNew = false;
        if ($operation instanceof \ApiPlatform\Metadata\Post) {
            $isNew = true;
        }
       
        $this->validator->validate($data, [
            'groups' => [
                'Default',
                $isNew ? 'Post' : 'Patch',
            ],
        ]);

        if ($isNew) {
            $object = new Archive();
        } else {
            $object = $this->em->find(Archive::class, $uriVariables['id']);
            if (null === $object) {
                throw new NotFoundHttpException('Archive not found');
            }
        }

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

        $object->setClient($this->security->getUser()->getUserIdentifier());

        $this->em->persist($object);
        $this->em->flush();
        
        $this->bus->dispatch(new BuildArchive($object->getId()));
        
        return $object;
    }
}
