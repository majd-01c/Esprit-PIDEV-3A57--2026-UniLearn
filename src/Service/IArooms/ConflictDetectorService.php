<?php

namespace App\Service\IArooms;

use App\Entity\IArooms\RoomConflict;
use App\Entity\IArooms\RoomBooking;
use App\Entity\IArooms\TimetableUpload;
use App\Repository\IArooms\RoomBookingRepository;

class ConflictDetectorService
{
    public function __construct(private readonly RoomBookingRepository $roomBookingRepository)
    {
    }

    /**
     * @return RoomConflict[]
     */
    public function detectConflicts(TimetableUpload $upload): array
    {
        $bookings = $this->roomBookingRepository->findByUploadOrdered($upload);
        $grouped = [];

        /** @var RoomBooking $booking */
        foreach ($bookings as $booking) {
            $roomName = $booking->getRoom()?->getName() ?? '';
            $dateKey = $booking->getBookingDate()?->format('Y-m-d') ?? '';
            $grouped[$roomName][$dateKey][] = $booking;
        }

        $conflicts = [];

        foreach ($grouped as $roomName => $dates) {
            foreach ($dates as $dateKey => $roomBookings) {
                $count = count($roomBookings);
                for ($i = 0; $i < $count; $i++) {
                    for ($j = $i + 1; $j < $count; $j++) {
                        $first = $roomBookings[$i];
                        $second = $roomBookings[$j];

                        if (!$this->overlaps($first, $second)) {
                            continue;
                        }

                        if ($first->getGroupName() === $second->getGroupName() && $first->getCourseName() === $second->getCourseName()) {
                            continue;
                        }

                        $overlapStart = $this->maxTime($first->getStartTime(), $second->getStartTime());
                        $overlapEnd = $this->minTime($first->getEndTime(), $second->getEndTime());

                        $conflict = (new RoomConflict())
                            ->setTimetableUpload($upload)
                            ->setRoom($first->getRoom())
                            ->setBookingDate($first->getBookingDate() ?? new \DateTimeImmutable($dateKey))
                            ->setStartTime($overlapStart)
                            ->setEndTime($overlapEnd)
                            ->setBookingAGroupName($first->getGroupName() ?? 'Unknown group')
                            ->setBookingACourseName($first->getCourseName() ?? 'Unknown course')
                            ->setBookingASourcePage($first->getSourcePage())
                            ->setBookingBGroupName($second->getGroupName() ?? 'Unknown group')
                            ->setBookingBCourseName($second->getCourseName() ?? 'Unknown course')
                            ->setBookingBSourcePage($second->getSourcePage())
                            ->setBookingAStartTime($first->getStartTime())
                            ->setBookingAEndTime($first->getEndTime())
                            ->setBookingBStartTime($second->getStartTime())
                            ->setBookingBEndTime($second->getEndTime())
                            ->setDescription(sprintf(
                                'Room %s is booked twice on %s between %s and %s.',
                                $roomName,
                                $dateKey,
                                $this->formatTime($overlapStart),
                                $this->formatTime($overlapEnd)
                            ));

                        $conflicts[] = $conflict;
                    }
                }
            }
        }

        return $conflicts;
    }

    private function overlaps(RoomBooking $first, RoomBooking $second): bool
    {
        if ($first->getStartTime() === null || $first->getEndTime() === null || $second->getStartTime() === null || $second->getEndTime() === null) {
            return false;
        }

        return $first->getStartTime() < $second->getEndTime()
            && $first->getEndTime() > $second->getStartTime();
    }

    private function maxTime(?\DateTimeImmutable $left, ?\DateTimeImmutable $right): ?\DateTimeImmutable
    {
        if ($left === null) {
            return $right;
        }

        if ($right === null) {
            return $left;
        }

        return $left > $right ? $left : $right;
    }

    private function minTime(?\DateTimeImmutable $left, ?\DateTimeImmutable $right): ?\DateTimeImmutable
    {
        if ($left === null) {
            return $right;
        }

        if ($right === null) {
            return $left;
        }

        return $left < $right ? $left : $right;
    }

    private function formatTime(?\DateTimeImmutable $time): string
    {
        return $time?->format('H:i') ?? '--:--';
    }
}
