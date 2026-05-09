<?php

namespace App\Repository\IArooms;

use App\Entity\IArooms\Room;
use App\Entity\IArooms\RoomBooking;
use App\Entity\IArooms\TimetableUpload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RoomBooking>
 */
class RoomBookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomBooking::class);
    }

    /**
     * @return RoomBooking[]
     */
    public function findByUploadOrdered(TimetableUpload $upload): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.room', 'r')->addSelect('r')
            ->andWhere('b.timetableUpload = :upload')
            ->setParameter('upload', $upload)
            ->orderBy('b.bookingDate', 'ASC')
            ->addOrderBy('b.startTime', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RoomBooking[]
     */
    public function findByUploadAndDate(TimetableUpload $upload, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.room', 'r')->addSelect('r')
            ->andWhere('b.timetableUpload = :upload')
            ->andWhere('b.bookingDate = :date')
            ->setParameter('upload', $upload)
            ->setParameter('date', $date)
            ->orderBy('b.startTime', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RoomBooking[]
     */
    public function findOverlappingBookings(TimetableUpload $upload, \DateTimeInterface $date, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.room', 'r')->addSelect('r')
            ->andWhere('b.timetableUpload = :upload')
            ->andWhere('b.bookingDate = :date')
            ->andWhere('b.startTime < :endTime')
            ->andWhere('b.endTime > :startTime')
            ->setParameter('upload', $upload)
            ->setParameter('date', $date)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->orderBy('r.name', 'ASC')
            ->addOrderBy('b.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Room[]
     */
    public function findRoomsForUpload(TimetableUpload $upload): array
    {
        return $this->createQueryBuilder('b')
            ->select('DISTINCT r')
            ->innerJoin('b.room', 'r')
            ->andWhere('b.timetableUpload = :upload')
            ->setParameter('upload', $upload)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
