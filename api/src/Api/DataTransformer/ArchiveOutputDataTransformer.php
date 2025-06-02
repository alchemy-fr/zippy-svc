<?php

declare(strict_types=1);

namespace App\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Api\ArchiveOutput;
use App\Entity\Archive;
use App\Security\JWTManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArchiveOutputDataTransformer implements DataTransformerInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private JWTManager $JWTManager;

    public function __construct(UrlGeneratorInterface $urlGenerator, JWTManager $JWTManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->JWTManager = $JWTManager;
    }

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
        $output->setExpiresAt($object->getExpiresAt());

        $output->setDownloadUrl($this->urlGenerator->generate('download_archive', [
            'id' => $object->getId(),
            'jwt' => $this->JWTManager->getArchiveJWT($object->getId()),
        ], UrlGeneratorInterface::ABSOLUTE_URL));

        return $output;
    }

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return ArchiveOutput::class === $to && $data instanceof Archive;
    }
}
