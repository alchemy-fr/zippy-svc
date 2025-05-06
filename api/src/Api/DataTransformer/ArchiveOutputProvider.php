<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use App\Entity\Archive;
use App\Api\ArchiveOutput;
use App\Security\JWTManager;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArchiveOutputProvider implements ProviderInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private JWTManager $JWTManager;
    private EntityManagerInterface $em;

    public function __construct(UrlGeneratorInterface $urlGenerator, JWTManager $JWTManager, EntityManagerInterface $em)
    {
        $this->urlGenerator = $urlGenerator;
        $this->JWTManager = $JWTManager;
        $this->em = $em;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ArchiveOutput
    {
        $archiveId= $uriVariables['id'];
        $object = $this->em->getRepository(Archive::class)->findOneBy(['id' => $archiveId]);

        if (null === $object) {
            throw new \RuntimeException('Archive not found');
        }

        $output = new ArchiveOutput();
        $output->setCreatedAt($object->getCreatedAt());
        $output->setUpdatedAt($object->getUpdatedAt());
        $output->setId($object->getId());
        $output->setIdentifier($object->getIdentifier());
        $output->setStatus($object->getStatusLabel());
        $output->setExpiresAt($object->getExpiresAt());

        $output->setDownloadUrl($this->urlGenerator->generate('download_archive', [
            'id' => $object->getId(),
            'jwt' => $this->JWTManager->getArchiveJWT($object->getId()),
        ], UrlGeneratorInterface::ABSOLUTE_URL));

        return $output;
    }
}
