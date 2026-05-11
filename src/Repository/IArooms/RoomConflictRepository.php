<?php

namespace App\Repository\IArooms;

use App\Entity\IArooms\RoomConflict;
use App\Entity\IArooms\TimetableUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoomConflict>
 */
class RoomConflictRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomConflict::class);
    }

    /**
     * @return RoomConflict[]
     */
    public function findOrdered(?TimetableUpload $upload = null): array
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.room', 'r')->addSelect('r')
            ->orderBy('c.bookingDate', 'DESC')
            ->addOrderBy('r.name', 'ASC')
            ->addOrderBy('c.startTime', 'ASC');

        if ($upload !== null) {
            $qb->andWhere('c.timetableUpload = :upload')
                ->setParameter('upload', $upload);
        }

        return $qb->getQuery()->getResult();
    }
}
