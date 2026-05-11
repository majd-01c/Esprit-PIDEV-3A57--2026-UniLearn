<?php

declare(strict_types=1);

namespace App\Service\JobOffer;

use App\Entity\JobApplication;
use App\Entity\JobOffer;
use App\Entity\JobOfferMeeting;
use App\Entity\User;
use App\Enum\JobApplicationStatus;
use App\Repository\JobOfferMeetingRepository;
use Doctrine\ORM\EntityManagerInterface;

final class JobOfferMeetingService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JobOfferMeetingRepository $meetingRepository,
    ) {
    }

    public function scheduleMeetingForPartner(
        JobApplication $application,
        ?string $title,
        ?string $description,
        \DateTimeInterface $scheduledAt,
        \DateTimeInterface $scheduledEndAt,
        User $reviewer,
        bool $flush = true,
    ): JobOfferMeeting {
        $this->assertAccepted($application);
        $this->assertReviewerCanManage($application->getOffer(), $reviewer);
        $this->assertFutureWindow($scheduledAt, $scheduledEndAt);

        $offer = $application->getOffer();
        $student = $application->getStudent();
        $partner = $offer?->getPartner() ?? $reviewer;

        if ($offer === null) {
            throw new \InvalidArgumentException('Application is not linked to a job offer.');
        }

        if ($student === null) {
            throw new \InvalidArgumentException('Application is not linked to a student.');
        }

        $meeting = $application->getMeeting()
            ?? $this->meetingRepository->findOneBy(['application' => $application])
            ?? new JobOfferMeeting();

        $meeting
            ->setApplication($application)
            ->setOffer($offer)
            ->setStudent($student)
            ->setPartner($partner)
            ->setTitle($this->normalizeTitle($title, $offer))
            ->setDescription($description)
            ->reschedule($scheduledAt, $scheduledEndAt);

        $this->em->persist($meeting);

        if ($flush) {
            $this->em->flush();
        }

        return $meeting;
    }

    public function cancelForApplication(JobApplication $application, bool $flush = true): void
    {
        $meeting = $application->getMeeting()
            ?? $this->meetingRepository->findOneBy(['application' => $application]);

        if ($meeting === null || $meeting->isEnded() || $meeting->isCancelled()) {
            return;
        }

        $meeting->cancel();

        if ($flush) {
            $this->em->flush();
        }
    }

    public function joinStudentMeeting(JobOfferMeeting $meeting, User $student): JobOfferMeeting
    {
        if ($meeting->getStudent()?->getId() !== $student->getId()) {
            throw new \LogicException('This meeting belongs to another student.');
        }

        return $this->joinCheckedMeeting($meeting);
    }

    public function joinPartnerMeeting(JobOfferMeeting $meeting, User $reviewer): JobOfferMeeting
    {
        $this->assertReviewerCanManage($meeting->getOffer(), $reviewer);

        return $this->joinCheckedMeeting($meeting);
    }

    public function endMeeting(JobOfferMeeting $meeting, User $reviewer): void
    {
        $this->assertReviewerCanManage($meeting->getOffer(), $reviewer);
        $meeting->end();
        $this->em->flush();
    }

    public function canJoinNow(?JobOfferMeeting $meeting): bool
    {
        return $meeting !== null && $meeting->canJoinNow();
    }

    public function buildJoinLockedMessage(JobOfferMeeting $meeting): string
    {
        if ($meeting->isEnded()) {
            return 'This meeting has ended.';
        }

        if ($meeting->isCancelled()) {
            return 'This meeting was cancelled.';
        }

        $start = $meeting->getScheduledAt();
        $end = $meeting->getScheduledEndAt();

        if ($start === null || $end === null) {
            return 'This meeting is not scheduled yet.';
        }

        $now = new \DateTimeImmutable();
        if ($now < $start) {
            return 'This meeting opens at ' . $start->format('M d, Y H:i') . '.';
        }

        if ($now > $end) {
            return 'This meeting ended at ' . $end->format('M d, Y H:i') . '.';
        }

        return 'This meeting can only be joined during its scheduled window.';
    }

    private function joinCheckedMeeting(JobOfferMeeting $meeting): JobOfferMeeting
    {
        $this->assertAccepted($meeting->getApplication());

        if (!$meeting->canJoinNow()) {
            throw new \LogicException($this->buildJoinLockedMessage($meeting));
        }

        if (!$meeting->isLive()) {
            $meeting->markLive();
            $this->em->flush();
        }

        return $meeting;
    }

    private function assertAccepted(?JobApplication $application): void
    {
        if ($application?->getStatus() !== JobApplicationStatus::ACCEPTED) {
            throw new \LogicException('A meeting can only be created or joined after the application is accepted.');
        }
    }

    private function assertReviewerCanManage(?JobOffer $offer, User $reviewer): void
    {
        if ($offer === null) {
            throw new \InvalidArgumentException('Meeting is not linked to a job offer.');
        }

        if (in_array('ROLE_ADMIN', $reviewer->getRoles(), true)) {
            return;
        }

        if ($offer->getPartner()?->getId() !== $reviewer->getId()) {
            throw new \LogicException('You can only manage meetings for your own job offers.');
        }
    }

    private function assertFutureWindow(\DateTimeInterface $scheduledAt, \DateTimeInterface $scheduledEndAt): void
    {
        $start = \DateTimeImmutable::createFromInterface($scheduledAt);
        $end = \DateTimeImmutable::createFromInterface($scheduledEndAt);

        if ($end <= $start) {
            throw new \InvalidArgumentException('Meeting end time must be after the start time.');
        }

        if ($end <= new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('Meeting end time must be in the future.');
        }
    }

    private function normalizeTitle(?string $title, JobOffer $offer): string
    {
        $title = trim((string) $title);
        if (mb_strlen($title) >= 3) {
            return $title;
        }

        $offerTitle = trim((string) $offer->getTitle());

        return $offerTitle === '' ? 'Job interview meeting' : 'Interview - ' . $offerTitle;
    }
}
