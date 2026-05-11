<?php

namespace App\Repository\IArooms;

use App\Entity\IArooms\TimetableUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimetableUpload>
 */
class TimetableUploadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimetableUpload::class);
    }

    public function findLatest(): ?TimetableUpload
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.uploadedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return TimetableUpload[]
     */
    public function findOrdered(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.uploadedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
