<?php

namespace App\Service\IArooms;

use App\Entity\IArooms\TimetableUpload;
use App\Repository\IArooms\RoomBookingRepository;
use App\Repository\IArooms\RoomRepository;
use DateTimeImmutable;
use DateTimeInterface;

class RoomAvailabilityService
{
    public const STANDARD_SLOTS = [
        ['label' => '09:00 - 10:30', 'start' => '09:00', 'end' => '10:30'],
        ['label' => '10:45 - 12:15', 'start' => '10:45', 'end' => '12:15'],
        ['label' => '13:30 - 15:00', 'start' => '13:30', 'end' => '15:00'],
        ['label' => '15:15 - 16:45', 'start' => '15:15', 'end' => '16:45'],
    ];

    public function __construct(
        private readonly RoomRepository $roomRepository,
        private readonly RoomBookingRepository $roomBookingRepository,
    ) {
    }

    public function getOccupiedRooms(DateTimeInterface $date, string $startTime, string $endTime, TimetableUpload $upload, ?string $roomFilter = null, ?string $buildingFilter = null): array
    {
        $bookings = $this->roomBookingRepository->findOverlappingBookings($upload, $date, $this->timeToDateTime($startTime), $this->timeToDateTime($endTime));

        $occupied = [];
        foreach ($bookings as $booking) {
            $roomName = $booking->getRoom()?->getName() ?? '';
            if (!$this->roomMatchesFilters($roomName, $roomFilter, $buildingFilter)) {
                continue;
            }

            $occupied[$roomName] = [
                'room' => $roomName,
                'group' => $booking->getGroupName(),
                'course' => $booking->getCourseName(),
                'date' => $booking->getBookingDate()?->format('Y-m-d'),
                'startTime' => $booking->getStartTime()?->format('H:i'),
                'endTime' => $booking->getEndTime()?->format('H:i'),
                'sourcePage' => $booking->getSourcePage(),
            ];
        }

        return array_values($occupied);
    }

    public function getEmptyRooms(DateTimeInterface $date, string $startTime, string $endTime, TimetableUpload $upload, ?string $roomFilter = null, ?string $buildingFilter = null): array
    {
        $rooms = $this->resolveRoomUniverse($upload, $roomFilter, $buildingFilter);
        $occupiedNames = array_map(static fn (array $room): string => $room['room'], $this->getOccupiedRooms($date, $startTime, $endTime, $upload, $roomFilter, $buildingFilter));

        return array_values(array_filter($rooms, static fn ($room) => !in_array($room->getName(), $occupiedNames, true)));
    }

    public function getAvailabilityForSlot(DateTimeInterface $date, string $startTime, string $endTime, TimetableUpload $upload, ?string $roomFilter = null, ?string $buildingFilter = null): array
    {
        $occupiedRooms = $this->getOccupiedRooms($date, $startTime, $endTime, $upload, $roomFilter, $buildingFilter);
        $emptyRooms = $this->getEmptyRooms($date, $startTime, $endTime, $upload, $roomFilter, $buildingFilter);

        return [
            'date' => $date->format('Y-m-d'),
            'startTime' => $startTime,
            'endTime' => $endTime,
            'emptyRooms' => $emptyRooms,
            'occupiedRooms' => $occupiedRooms,
        ];
    }

    public function getAvailabilityForDay(DateTimeInterface $date, TimetableUpload $upload, ?string $roomFilter = null, ?string $buildingFilter = null): array
    {
        $slots = [];
        foreach (self::STANDARD_SLOTS as $slot) {
            $availability = $this->getAvailabilityForSlot($date, $slot['start'], $slot['end'], $upload, $roomFilter, $buildingFilter);
            $slots[] = [
                'date' => $date->format('Y-m-d'),
                'dayName' => $this->dayLabel($date),
                'slot' => $slot['label'],
                'startTime' => $slot['start'],
                'endTime' => $slot['end'],
                'emptyCount' => count($availability['emptyRooms']),
                'occupiedCount' => count($availability['occupiedRooms']),
                'emptyRooms' => $availability['emptyRooms'],
                'occupiedRooms' => $availability['occupiedRooms'],
            ];
        }

        return $slots;
    }

    public function getRoomsEmptyAllDay(DateTimeInterface $date, TimetableUpload $upload, ?string $roomFilter = null, ?string $buildingFilter = null): array
    {
        $rooms = $this->resolveRoomUniverse($upload, $roomFilter, $buildingFilter);
        $bookings = $this->roomBookingRepository->findByUploadAndDate($upload, $date);

        $occupiedRooms = [];
        foreach ($bookings as $booking) {
            $roomName = $booking->getRoom()?->getName();
            if ($roomName !== null && $this->roomMatchesFilters($roomName, $roomFilter, $buildingFilter)) {
                $occupiedRooms[$roomName] = true;
            }
        }

        return array_values(array_filter($rooms, static fn ($room) => !isset($occupiedRooms[$room->getName()])));
    }

    /**
    * @return array<int, \App\Entity\IArooms\Room>
     */
    private function resolveRoomUniverse(TimetableUpload $upload, ?string $roomFilter = null, ?string $buildingFilter = null): array
    {
        $rooms = $this->roomRepository->findObservedForUpload($upload);

        return array_values(array_filter($rooms, fn ($room) => $this->roomMatchesFilters($room->getName() ?? '', $roomFilter, $buildingFilter)));
    }

    private function roomMatchesFilters(string $roomName, ?string $roomFilter, ?string $buildingFilter): bool
    {
        $normalized = mb_strtoupper($roomName);

        if ($roomFilter !== null && $roomFilter !== '' && !str_contains($normalized, mb_strtoupper($roomFilter))) {
            return false;
        }

        if ($buildingFilter !== null && $buildingFilter !== '' && !str_starts_with($normalized, mb_strtoupper($buildingFilter))) {
            return false;
        }

        return true;
    }

    private function timeToDateTime(string $time): DateTimeImmutable
    {
        return DateTimeImmutable::createFromFormat('H:i', $time) ?: new DateTimeImmutable('today ' . $time);
    }

    private function dayLabel(DateTimeInterface $date): string
    {
        return match ((int) $date->format('N')) {
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            default => 'Dimanche',
        };
    }
}
