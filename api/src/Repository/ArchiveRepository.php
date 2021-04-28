<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Archive;
use DateTime;
use Doctrine\ORM\EntityRepository;

class ArchiveRepository extends EntityRepository
{
    /**
     * @return Archive[]|iterable
     */
    public function getExpired(DateTime $now = null): iterable
    {
        $now ??= new DateTime();

        return $this->createQueryBuilder('a')
            ->select('a')
            ->andWhere('a.expiresAt IS NOT NULL AND a.expiresAt < :date')
            ->setParameter('date', $now->format('Y-m-d H:i:s'))
            ->getQuery()
            ->toIterable();
    }
}
