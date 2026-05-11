<?php

namespace App\Repository\IArooms;

use App\Entity\IArooms\Room;
use App\Entity\IArooms\TimetableUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * @return Room[]
     */
    public function findObservedForUpload(TimetableUpload $upload): array
    {
        return $this->createQueryBuilder('r')
            ->select('DISTINCT r')
            ->innerJoin('r.roomBookings', 'b')
            ->andWhere('b.timetableUpload = :upload')
            ->setParameter('upload', $upload)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
