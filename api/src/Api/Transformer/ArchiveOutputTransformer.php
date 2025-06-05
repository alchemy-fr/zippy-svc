<?php

declare(strict_types=1);

namespace App\Api\Transformer;

use App\Api\ArchiveOutput;
use App\Security\JWTManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ArchiveOutputTransformer
{
    private UrlGeneratorInterface $urlGenerator;
    private JWTManager $JWTManager;


    public function __construct(UrlGeneratorInterface $urlGenerator, JWTManager $JWTManager)
    {
        $this->urlGenerator = $urlGenerator;
        $this->JWTManager = $JWTManager;
    }

    public function transform(mixed $data): ArchiveOutput
    {        
        $output = new ArchiveOutput();
        $output->setCreatedAt($data->getCreatedAt());
        $output->setUpdatedAt($data->getUpdatedAt());
        $output->setId($data->getId());
        $output->setIdentifier($data->getIdentifier());
        $output->setStatus($data->getStatusLabel());
        $output->setExpiresAt($data->getExpiresAt());

        $output->setDownloadUrl($this->urlGenerator->generate('download_archive', [
            'id' => $data->getId(),
            'jwt' => $this->JWTManager->getArchiveJWT($data->getId()),
        ], UrlGeneratorInterface::ABSOLUTE_URL));

        return $output;
    }
}
