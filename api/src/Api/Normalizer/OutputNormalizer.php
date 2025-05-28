<?php

declare(strict_types=1);

namespace App\Api\Normalizer;

use App\Api\Transformer\ArchiveOutputTransformer;
use App\Entity\Archive;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsDecorator('api_platform.jsonld.normalizer.item')]
final class OutputNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;
    private ArchiveOutputTransformer $archiveOutputTransformer;

    public function __construct(NormalizerInterface $decorated, ArchiveOutputTransformer $archiveOutputTransformer)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->archiveOutputTransformer = $archiveOutputTransformer;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        if ($object instanceof Archive) {
            $outputArchive = $this->archiveOutputTransformer->transform($object);

            return $this->decorated->normalize($outputArchive, $format, $context);
        }

        return $this->decorated->normalize($object, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
