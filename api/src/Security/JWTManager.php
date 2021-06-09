<?php

declare(strict_types=1);

namespace App\Security;

use DateTimeImmutable;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;

class JWTManager
{
    private string $signingKey;
    private int $ttl;

    public function __construct(string $signingKey, int $ttl)
    {
        $this->signingKey = $signingKey;
        $this->ttl = $ttl;
    }

    public function getArchiveJWT(string $archiveId, ?int $ttl = null): string
    {
        $config = $this->getConfig();
        $token = $config->builder()
            ->identifiedBy($archiveId)
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt((new DateTimeImmutable())->setTimestamp(time() + ($ttl ?? $this->ttl)))
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

    public function validateJWT(string $uri, string $jwt): void
    {
        $config = $this->getConfig();
        $token = $config->parser()->parse($jwt);
        assert($token instanceof UnencryptedToken);

        $uri = preg_replace('#(&|\?)jwt=.+$#', '', $uri);

        $config->setValidationConstraints(
            new Constraint\LooseValidAt(
                new SystemClock(new \DateTimeZone('UTC')),
                new \DateInterval('PT30S')
            ),
            new Constraint\IdentifiedBy($uri),
        );

        $constraints = $config->validationConstraints();

        $config->validator()->assert($token, ...$constraints);
    }

    private function getConfig(): Configuration
    {
        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->signingKey)
        );
    }
}
