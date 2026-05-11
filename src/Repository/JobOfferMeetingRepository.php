<?php

namespace App\Repository;

use App\Entity\JobOfferMeeting;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobOfferMeeting>
 */
class JobOfferMeetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOfferMeeting::class);
    }

    /** @return JobOfferMeeting[] */
    public function findByStudent(User $student): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('application', 'offer', 'partner')
            ->join('m.application', 'application')
            ->join('m.offer', 'offer')
            ->join('m.partner', 'partner')
            ->andWhere('m.student = :student')
            ->setParameter('student', $student)
            ->orderBy('m.scheduledAt', 'ASC')
            ->addOrderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return JobOfferMeeting[] */
    public function findByPartner(User $partner): array
    {
        return $this->createQueryBuilder('m')
            ->addSelect('application', 'offer', 'student')
            ->join('m.application', 'application')
            ->join('m.offer', 'offer')
            ->join('m.student', 'student')
            ->andWhere('m.partner = :partner')
            ->setParameter('partner', $partner)
            ->orderBy('m.scheduledAt', 'ASC')
            ->addOrderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @param int[] $applicationIds */
    public function findByApplicationIds(array $applicationIds): array
    {
        if ($applicationIds === []) {
            return [];
        }

        return $this->createQueryBuilder('m')
            ->addSelect('application', 'offer', 'student', 'partner')
            ->join('m.application', 'application')
            ->join('m.offer', 'offer')
            ->join('m.student', 'student')
            ->join('m.partner', 'partner')
            ->andWhere('application.id IN (:applicationIds)')
            ->setParameter('applicationIds', array_values(array_unique($applicationIds)))
            ->orderBy('m.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
